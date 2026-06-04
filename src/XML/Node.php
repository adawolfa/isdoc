<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\XML;

use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\Schema\Extension;
use Adawolfa\ISDOC\Schema\XMLNamespace;
use BackedEnum;
use BcMath\Number;
use Countable;
use DateTimeImmutable;
use DateTimeInterface;
use Dom\Document;
use Dom\Element;
use Dom\Text;
use Dom\XMLDocument;
use PropertyHookType;
use ReflectionClass;
use ReflectionEnum;
use ReflectionProperty;
use Throwable;
use ValueError;
use WeakMap;

/**
 * Namespace-aware façade over a native {@see Element}, the single source of truth for value access.
 *
 * Reads return {@code null} for an absent element or attribute and throw {@see Exception} (path-tagged) only
 * when a value that *is* present cannot be cast — satisfying the access-time tolerance model. The owning schema
 * view decides whether a {@code null} is acceptable (optional) or must raise missing-required.
 *
 * Writes create child elements lazily and keep them in XSD-sequence order, driven by the {@see $order} list the
 * owning view supplies for its element type. A child entity's element is grafted in via {@code adoptNode} (a
 * move, not a copy), so cached child views stay live after attachment.
 *
 * A node is reachable from user code through the public {@see Entity::$node} escape hatch, so a deliberate subset
 * is supported API for reading and writing unmapped content (extension elements, non-standard attributes): the
 * {@see has()} probe, the {@code get*}/{@code set*} scalar accessors, the {@see $text} body, {@see getChild()}/
 * {@see getChildren()}, {@see setChild()}/{@see addChild()}/{@see remove()}, and the raw {@see $dom} element.
 * Everything tagged {@code @internal} is engine plumbing — binding, order/namespace inference, reference minting,
 * the required-or-throw readers — and may change without notice; it is not meant to be called from outside the
 * package.
 */
final class Node
{

	public const string Namespace = 'http://isdoc.cz/namespace/2013';

	/** @var array<class-string, ReflectionClass<Entity>> */
	private static array $reflections = [];

	/** @var array<class-string<Entity>, list<string>> inferred child-element order per entity class */
	private static array $orders = [];

	/**
	 * While inferring an entity's order, the child element local names looked up via {@see element()} are
	 * recorded here in access order; {@code null} disables recording. Each ordered member's get-hook performs
	 * exactly one such lookup, so probing the members in declaration order reconstructs the sequence.
	 *
	 * @var list<string>|null
	 */
	private static ?array $elementTrace = null;

	/**
	 * While discovering a collection's repeated item element, the local names looked up via {@see getChildren()}
	 * are recorded here; {@code null} disables recording. Kept separate from {@see $elementTrace} so an
	 * unwrapped sibling collection's {@code children()} read never leaks into its parent's member order.
	 *
	 * @var list<string>|null
	 */
	private static ?array $childrenTrace = null;

	/** @var array<class-string<BackedEnum>, bool> whether the backed enum's backing type is int */
	private static array $enumBackings = [];

	/** @var array<class-string, array{string, ?string}> {@see XMLNamespace} (uri, prefix) per entity class, cached */
	private static array $namespaces = [];

	/**
	 * Canonical view for an element: the same {@see Element} always maps back to the same {@see Entity}, so
	 * a reference resolved through an {@code @ref} returns the very object the collection iteration produced,
	 * and a user-supplied subclass (decorated totals, remote supplements) survives a round-trip through a get-hook.
	 *
	 * @var WeakMap<Element, Entity>|null
	 */
	private static ?WeakMap $views = null;

	/**
	 * Pending {@code wrapper element => referenced target element} links recorded by reference set-hooks; the
	 * sequential {@code @id}/{@code @ref} pairs are minted from these in {@see finalizeReferences()} just before
	 * serialization, so only elements that are actually referenced ever receive an id.
	 *
	 * @var WeakMap<Element, Element>|null
	 */
	private static ?WeakMap $pendingReferences = null;

	/**
	 * @param list<string> $order canonical order of child element local names, for sequence-correct insertion
	 * @param string       $namespace the namespace this node's child elements are matched and created in
	 * @param ?string      $prefix the prefix new child elements carry ({@code null} ⇒ a default-namespace declaration)
	 */
	private function __construct(
		private readonly Element $element,
		private readonly array   $order = [],
		private readonly string  $namespace = self::Namespace,
		private readonly ?string $prefix = null,
	)
	{
	}

	/**
	 * Binds to an existing, parsed element (read path).
	 *
	 * @internal
	 * @param list<string> $order
	 */
	public static function wrap(Element $element, array $order = []): self
	{
		return new self($element, $order);
	}

	/**
	 * Creates a fresh, detached element in a scratch document (write path).
	 *
	 * The element tag and child-element order can be given explicitly; pass nothing and they are inferred from
	 * the caller — see {@see entity()}, which schema entities use so they no longer carry Element/Order constants.
	 * The namespace defaults to the ISDOC one; a foreign namespace (with an optional prefix) is only used by
	 * {@see Extension} types.
	 *
	 * @internal
	 * @param list<string> $order
	 */
	public static function create(
		string  $name,
		array   $order = [],
		string  $namespace = self::Namespace,
		?string $prefix = null,
	): self
	{
		$qualified = $prefix === null ? $name : "$prefix:$name";

		return new self(XMLDocument::createEmpty()->createElementNS($namespace, $qualified), $order, $namespace, $prefix);
	}

	/**
	 * Creates a fresh node for an entity class, inferring its element tag (the class basename) and its
	 * child-element order (from the property hooks; see {@see orderFor()}) instead of reading constants. The
	 * namespace is the ISDOC one for every type except an {@see Extension} carrying an {@see XMLNamespace}.
	 *
	 * @internal
	 * @param class-string<Entity> $class
	 */
	public static function entity(string $class): self
	{
		return self::create(self::shortName($class), self::orderFor($class), self::namespaceFor($class), self::prefixFor($class));
	}

	/**
	 * The canonical child-element order for an entity class, inferred once and cached: a collection's repeated
	 * item element first (if any), then each ordered member in declaration order. Attributes, simple content,
	 * references and unwrapped sibling collections contribute nothing — their hooks don't perform the singular
	 * child lookup the inference traces.
	 *
	 * @internal
	 * @param class-string<Entity> $class
	 * @return list<string>
	 */
	public static function orderFor(string $class): array
	{
		if (isset(self::$orders[$class])) {
			return self::$orders[$class];
		}

		// Seed before probing: a member get-hook reaches back into this entity's own node (and so into orderFor
		// for the same class) — the seed makes that re-entrant call return the empty order instead of recursing.
		self::$orders[$class] = [];

		return self::$orders[$class] = self::inferOrder($class);
	}

	/**
	 * Reconstructs an entity class's child-element order by exercising its property hooks against a throwaway
	 * instance and recording which child elements each one touches.
	 *
	 * @param class-string<Entity> $class
	 * @return list<string>
	 */
	private static function inferOrder(string $class): array
	{
		// A member get-hook may bind a nested entity, which re-enters inference for a *different* class; save and
		// restore the trace buffers so that nested pass cannot clobber the one in progress.
		$savedElement  = self::$elementTrace;
		$savedChildren = self::$childrenTrace;
		self::$elementTrace = self::$childrenTrace = null;

		try {

			$reflection = new ReflectionClass($class);
			$probe      = $reflection->newInstanceWithoutConstructor();
			$order      = [];

			// A collection keeps its repeated item element first; count() looks it up via children().
			if ($probe instanceof Countable) {
				self::$childrenTrace = [];
				$probe->count();
				$order = self::drainChildrenTrace();
			}

			// Each ordered member's get-hook performs exactly one singular child lookup; invoking the hooks in
			// declaration order records the element names in sequence. The hook is run through its ReflectionMethod
			// (a missing-required value still records its lookup before it throws, hence the catch).
			self::$elementTrace = [];

			foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {

				if ($property->getName() === 'node') {
					continue;
				}

				$get = $property->getHook(PropertyHookType::Get);

				if ($get === null) {
					continue;
				}

				try {
					$get->invoke($probe);
				} catch (Throwable) {
					// an unreadable value (e.g. missing-required) has already recorded its lookup; ignore the throw
				}

			}

			foreach (self::drainElementTrace() as $name) {
				$order[] = $name;
			}

			return array_values(array_unique($order));

		} finally {
			self::$elementTrace  = $savedElement;
			self::$childrenTrace = $savedChildren;
		}
	}

	/** @return list<string> */
	private static function drainChildrenTrace(): array
	{
		$trace = self::$childrenTrace ?? [];
		self::$childrenTrace = null;

		return $trace;
	}

	/** @return list<string> */
	private static function drainElementTrace(): array
	{
		$trace = self::$elementTrace ?? [];
		self::$elementTrace = null;

		return $trace;
	}

	/** @param class-string $class */
	private static function shortName(string $class): string
	{
		$pos = strrpos($class, '\\');

		return $pos === false ? $class : substr($class, $pos + 1);
	}

	/**
	 * The namespace an entity class's child elements live in: the ISDOC namespace for every type, except an
	 * {@see Extension} carrying an {@see XMLNamespace} attribute, which names its own. Cached per class.
	 *
	 * @internal
	 * @param class-string $class
	 */
	public static function namespaceFor(string $class): string
	{
		return self::xmlNamespace($class)[0];
	}

	/**
	 * The element prefix an {@see Extension} class declares via {@see XMLNamespace}, or {@code null} for a
	 * default-namespace declaration (and for every non-extension type).
	 *
	 * @internal
	 * @param class-string $class
	 */
	public static function prefixFor(string $class): ?string
	{
		return self::xmlNamespace($class)[1];
	}

	/**
	 * @param class-string $class
	 * @return array{string, ?string} (namespace uri, prefix)
	 */
	private static function xmlNamespace(string $class): array
	{
		if (isset(self::$namespaces[$class])) {
			return self::$namespaces[$class];
		}

		$namespace = self::Namespace;
		$prefix    = null;

		// Only extensions ever carry a foreign namespace, so non-extension types skip the attribute reflection.
		if (is_a($class, Extension::class, true)) {

			$attributes = new ReflectionClass($class)->getAttributes(XMLNamespace::class);

			if (isset($attributes[0])) {
				$declared  = $attributes[0]->newInstance();
				$namespace = $declared->uri;
				$prefix    = $declared->prefix;
			}

		}

		return self::$namespaces[$class] = [$namespace, $prefix];
	}

	/**
	 * Returns the same node viewed with a different child-element order, keeping its namespace.
	 *
	 * @internal
	 * @param list<string> $order
	 */
	public function withOrder(array $order): self
	{
		return new self($this->element, $order, $this->namespace, $this->prefix);
	}

	/**
	 * Returns the same node viewed in a different namespace (with an optional element prefix), keeping its order —
	 * used to re-view an {@see Extension}'s element so its child reads and writes resolve in the declared namespace.
	 *
	 * @internal
	 */
	public function withNamespace(string $namespace, ?string $prefix = null): self
	{
		return new self($this->element, $this->order, $namespace, $prefix);
	}

	/** The backing element — the escape hatch for reading and writing unmapped content directly. */
	public Element $dom {
		get => $this->element;
	}

	/**
	 * Document path of this element, optionally extended by a child element or {@code @attribute} name.
	 *
	 * @internal
	 */
	public function getPath(?string $name = null): string
	{
		$parts = [];

		for ($element = $this->element; $element instanceof Element; $element = $element->parentElement) {
			array_unshift($parts, $element->localName);
		}

		$path = implode('/', $parts);

		return $name === null ? $path : "$path/$name";
	}

	public function has(string $name): bool
	{
		if (str_starts_with($name, '@')) {
			return $this->element->hasAttribute(substr($name, 1));
		}

		return $this->element($name) !== null;
	}

	public function getString(string $name): ?string
	{
		if (str_starts_with($name, '@')) {
			$attribute = substr($name, 1);
			return $this->element->hasAttribute($attribute) ? $this->element->getAttribute($attribute) : null;
		}

		$element = $this->element($name);

		// A present-but-empty element reads as an empty string (it exists); only an absent element is null.
		return $element === null ? null : (self::directText($element) ?? '');
	}

	/** @throws Exception */
	public function getInt(string $name): ?int
	{
		$value = $this->getString($name);

		if ($value === null || trim($value) === '') {
			return null;
		}

		$trimmed = trim($value);

		if (preg_match('~^[+-]?\d+$~', $trimmed) !== 1) {
			throw Exception::malformedValue($this->getPath($name), $value, 'integer');
		}

		return (int) $trimmed;
	}

	/** @throws Exception */
	public function getBool(string $name): ?bool
	{
		$value = $this->getString($name);

		if ($value === null || trim($value) === '') {
			return null;
		}

		return match (strtolower(trim($value))) {
			'1', 'true'  => true,
			'0', 'false' => false,
			default      => throw Exception::malformedValue($this->getPath($name), $value, 'boolean'),
		};
	}

	/** @throws Exception */
	public function getDate(string $name): ?DateTimeImmutable
	{
		$value = $this->getString($name);

		if ($value === null || trim($value) === '') {
			return null;
		}

		$date = DateTimeImmutable::createFromFormat('Y-m-d', trim($value));

		if ($date === false) {
			throw Exception::malformedValue($this->getPath($name), $value, 'date');
		}

		return $date->setTime(0, 0);
	}

	/**
	 * Required-value read: the {@see getString()} value, or a path-tagged {@see Exception} when the element or
	 * attribute is absent. The missing-required half of the access-time tolerance model, hoisted out of the
	 * schema get-hooks so a required scalar reads as a single expression with the throw declared, not inlined.
	 *
	 * @internal
	 * @throws Exception
	 */
	public function getStringOrThrow(string $name): string
	{
		return $this->getString($name) ?? throw Exception::missingValue($this->getPath($name));
	}

	/**
	 * Required-value read: the {@see getInt()} value, or a path-tagged {@see Exception} when absent.
	 *
	 * @internal
	 * @throws Exception
	 */
	public function getIntOrThrow(string $name): int
	{
		return $this->getInt($name) ?? throw Exception::missingValue($this->getPath($name));
	}

	/**
	 * Required-value read: the {@see getBool()} value, or a path-tagged {@see Exception} when absent.
	 *
	 * @internal
	 * @throws Exception
	 */
	public function getBoolOrThrow(string $name): bool
	{
		return $this->getBool($name) ?? throw Exception::missingValue($this->getPath($name));
	}

	/**
	 * Required-value read: the {@see getDate()} value, or a path-tagged {@see Exception} when absent.
	 *
	 * @internal
	 * @throws Exception
	 */
	public function getDateOrThrow(string $name): DateTimeImmutable
	{
		return $this->getDate($name) ?? throw Exception::missingValue($this->getPath($name));
	}

	/**
	 * The named element or attribute parsed as a case of the given backed enum, or {@code null} when absent
	 * (or present-but-empty). A present value outside the enum throws a path-tagged {@see Exception}, the same
	 * access-time tolerance applied by {@see getInt()}/{@see getDate()}.
	 *
	 * @template T of BackedEnum
	 * @param class-string<T> $enum
	 * @return T|null
	 * @throws Exception
	 */
	public function getEnum(string $name, string $enum): ?BackedEnum
	{
		if (self::enumIsInt($enum)) {
			$value = $this->getInt($name);
		} else {
			$value = $this->getString($name);

			if ($value !== null && trim($value) === '') {
				$value = null;
			}
		}

		if ($value === null) {
			return null;
		}

		$case = $enum::tryFrom($value);

		if ($case === null) {
			$short = ($pos = strrpos($enum, '\\')) === false ? $enum : substr($enum, $pos + 1);
			throw Exception::malformedValue($this->getPath($name), (string) $value, $short);
		}

		return $case;
	}

	/**
	 * Required-value read: the {@see getEnum()} case, or a path-tagged {@see Exception} when absent.
	 *
	 * @internal
	 * @template T of BackedEnum
	 * @param class-string<T> $enum
	 * @return T
	 * @throws Exception
	 */
	public function getEnumOrThrow(string $name, string $enum): BackedEnum
	{
		return $this->getEnum($name, $enum) ?? throw Exception::missingValue($this->getPath($name));
	}

	/**
	 * The named element or attribute as an exact {@see Number}, or {@code null} when absent (or present-but-empty).
	 * A present-but-non-decimal value throws a path-tagged {@see Exception}, the same access-time tolerance applied
	 * by {@see getInt()}/{@see getDate()}.
	 *
	 * @throws Exception
	 */
	public function getNumber(string $name): ?Number
	{
		$value = $this->getString($name);

		if ($value === null || trim($value) === '') {
			return null;
		}

		$trimmed = trim($value);

		// is_numeric() both narrows the value to a numeric-string and rejects outright garbage; the try/catch
		// still guards the numeric-but-not-decimal forms (e.g. exponentials) that BcMath\Number refuses.
		if (is_numeric($trimmed)) {
			try {
				return new Number($trimmed);
			} catch (ValueError) {
				// fall through to the malformed-value exception below
			}
		}

		throw Exception::malformedValue($this->getPath($name), $value, 'decimal');
	}

	/**
	 * Required-value read: the {@see getNumber()} value, or a path-tagged {@see Exception} when absent.
	 *
	 * @internal
	 * @throws Exception
	 */
	public function getNumberOrThrow(string $name): Number
	{
		return $this->getNumber($name) ?? throw Exception::missingValue($this->getPath($name));
	}

	/** Simple content (the text body) of this element; assigning {@code null} or {@code ''} empties it. */
	public ?string $text {
		get => self::directText($this->element);
		set { self::setElementText($this->element, $value); }
	}

	/**
	 * The named child as a raw node, or — when a class is given — bound to its entity view of that class.
	 *
	 * @template T of Entity
	 * @param class-string<T>|null $class
	 * @return ($class is null ? Node|null : T|null)
	 */
	public function getChild(string $name, ?string $class = null): Node|Entity|null
	{
		$element = $this->element($name);

		if ($element === null) {
			return null;
		}

		$child = new self($element);

		return $class === null ? $child : $child->bind($class);
	}

	/**
	 * Required-value read: the named child bound to its entity view, or a path-tagged {@see Exception} when
	 * the child element is absent.
	 *
	 * @internal
	 * @template T of Entity
	 * @param class-string<T> $class
	 * @return T
	 * @throws Exception
	 */
	public function getChildOrThrow(string $name, string $class): Entity
	{
		return $this->getChild($name, $class) ?? throw Exception::missingValue($this->getPath($name));
	}

	/**
	 * The named child bound to its entity view, creating an empty element in XSD-sequence order if absent
	 * (for append-only collections, whose wrapper must exist before items can be added).
	 *
	 * @internal
	 * @template T of Entity
	 * @param class-string<T> $class
	 * @return T
	 */
	public function ensureChild(string $name, string $class): Entity
	{
		return ($this->getChild($name) ?? new self($this->insert($name)))->bind($class);
	}

	/**
	 * The named children as raw nodes, or — when a class is given — bound to their entity views of that class.
	 *
	 * @template T of Entity
	 * @param class-string<T>|null $class
	 * @return ($class is null ? list<Node> : list<T>)
	 */
	public function getChildren(string $name, ?string $class = null): array
	{
		if (self::$childrenTrace !== null) {
			self::$childrenTrace[] = $name;
		}

		$children = [];

		foreach ($this->element->childNodes as $node) {
			if ($node instanceof Element && $this->matches($node, $name)) {
				$child      = new self($node);
				$children[] = $class === null ? $child : $child->bind($class);
			}
		}

		return $children;
	}

	/**
	 * Binds this node to the canonical view of the given entity class, reusing a previously bound view of the
	 * same element when one exists (preserving object identity for references and user-supplied subclasses).
	 *
	 * No constructor runs (the values live in the DOM, read lazily), so this never depends on an entity having
	 * an argument-less constructor; the view's own setter applies its child order to the node.
	 *
	 * @internal
	 * @template T of Entity
	 * @param class-string<T> $class
	 * @return T
	 */
	public function bind(string $class): Entity
	{
		$views  = self::views();
		$cached = $views[$this->element] ?? null;

		if ($cached instanceof $class) {
			return $cached;
		}

		$entity = $this->view($class);

		if ($cached === null) {
			$views[$this->element] = $entity;
		}

		return $entity;
	}

	/**
	 * Binds this node to a fresh, uncached view of the given entity class — used for projections that must stay
	 * distinct from the element's canonical view (inline references) or that share their parent's element
	 * (unwrapped sibling collections, which must not displace the parent in the view cache).
	 *
	 * @internal
	 * @template T of Entity
	 * @param class-string<T> $class
	 * @return T
	 */
	public function view(string $class): Entity
	{
		$reflection = self::$reflections[$class] ?? null;

		if ($reflection === null) {
			/** @var ReflectionClass<Entity> $reflection */
			$reflection = new ReflectionClass($class);
			self::$reflections[$class] = $reflection;
		}

		$entity       = $reflection->newInstanceWithoutConstructor();
		$entity->node = $this;

		/** @var T $entity */
		return $entity;
	}

	public function setString(string $name, ?string $value): void
	{
		if (str_starts_with($name, '@')) {
			$attribute = substr($name, 1);

			if ($value === null) {
				$this->element->removeAttribute($attribute);
			} else {
				$this->element->setAttribute($attribute, $value);
			}

			return;
		}

		if ($value === null) {
			$this->remove($name);
			return;
		}

		self::setElementText($this->element($name) ?? $this->insert($name), $value);
	}

	public function setInt(string $name, ?int $value): void
	{
		$this->setString($name, $value === null ? null : (string) $value);
	}

	public function setBool(string $name, ?bool $value): void
	{
		$this->setString($name, $value === null ? null : ($value ? 'true' : 'false'));
	}

	public function setDate(string $name, ?DateTimeInterface $value): void
	{
		$this->setString($name, $value?->format('Y-m-d'));
	}

	public function setEnum(string $name, ?BackedEnum $value): void
	{
		$this->setString($name, $value === null ? null : (string) $value->value);
	}

	/** Stores a decimal: the {@see Number} is written as its canonical string form, {@code null} removes it. */
	public function setNumber(string $name, ?Number $value): void
	{
		$this->setString($name, $value === null ? null : (string) $value);
	}

	public function setChild(string $name, Node|Entity|null $child): void
	{
		$this->remove($name);

		if ($child !== null) {
			$this->graft($name, $child);
		}
	}

	public function addChild(string $name, Node|Entity $child): void
	{
		$this->graft($name, $child);
	}

	/**
	 * Records a {@code wrapper => target} reference link, resolved to {@code @id}/{@code @ref} on finalize.
	 *
	 * @internal
	 */
	public function setReference(Entity $target): void
	{
		self::pendingReferences()[$this->element] = $target->node->dom;
	}

	/**
	 * Resolves an {@code @ref} to the element carrying the matching {@code @id} under the same wrapper-name scope
	 * (so the same id may legitimately appear under {@code OrderReference} and {@code DeliveryNoteReference}).
	 *
	 * @internal
	 * @template T of Entity
	 * @param class-string<T> $class
	 * @return T
	 * @throws Exception
	 */
	public function getReference(string $class, string $ref): Entity
	{
		$target = self::find($this->root(), $this->element->localName, $ref);

		if ($target === null) {
			throw Exception::unresolvedReference($this->getPath(), $ref);
		}

		return new self($target)->bind($class);
	}

	public function remove(string $name): void
	{
		if (str_starts_with($name, '@')) {
			$this->element->removeAttribute(substr($name, 1));
			return;
		}

		foreach ($this->getChildren($name) as $child) {
			$this->element->removeChild($child->element);
		}
	}

	/**
	 * Mints the deferred {@code @id}/{@code @ref} pairs for every reference recorded under the given root's
	 * document: each referenced target gets a sequential id, unique within the document and counted from 1,
	 * each wrapper the matching ref. The counter is local to this call, so every encoded document starts over
	 * at 1. Only actually-referenced elements receive an id.
	 *
	 * @internal
	 */
	public static function finalizeReferences(Element $root): void
	{
		$document = $root->ownerDocument;
		$counter  = 0;
		$assigned = [];

		foreach (self::pendingReferences() as $wrapper => $target) {

			if ($wrapper->ownerDocument !== $document) {
				continue;
			}

			$key = spl_object_id($target);

			if (!isset($assigned[$key])) {

				$id = $target->getAttribute('id') ?? '';

				if ($id === '') {
					$id = (string) ++$counter;
					$target->setAttribute('id', $id);
				}

				$assigned[$key] = $id;

			}

			$wrapper->setAttribute('ref', $assigned[$key]);

		}
	}

	private function element(string $name): ?Element
	{
		if (self::$elementTrace !== null) {
			self::$elementTrace[] = $name;
		}

		foreach ($this->element->childNodes as $node) {
			if ($node instanceof Element && $this->matches($node, $name)) {
				return $node;
			}
		}

		return null;
	}

	private function document(): Document
	{
		$document = $this->element->ownerDocument;
		assert($document !== null);

		return $document;
	}

	/** The root element of this element's document. */
	private function root(): Element
	{
		$element = $this->element;

		while ($element->parentElement instanceof Element) {
			$element = $element->parentElement;
		}

		return $element;
	}

	/** Depth-first search for an element with the given local name carrying {@code @id} equal to {@code $id}. */
	private static function find(Element $element, string $localName, string $id): ?Element
	{
		if ($element->localName === $localName && $element->getAttribute('id') === $id) {
			return $element;
		}

		foreach ($element->childNodes as $node) {

			if (!$node instanceof Element) {
				continue;
			}

			$found = self::find($node, $localName, $id);

			if ($found !== null) {
				return $found;
			}

		}

		return null;
	}

	/**
	 * Grafts a child entity's element under this one: moves it across documents (a move, so the child view
	 * stays live), renames it to the parent-mapped tag in place, inserts it in XSD-sequence order, and — for a
	 * full entity rather than a bare node — records it as the element's canonical view.
	 */
	private function graft(string $name, Node|Entity $child): void
	{
		if ($child instanceof Entity) {
			$node                         = $child->node;
			self::views()[$node->element] = $child;
		} else {
			$node = $child;
		}

		$element  = $node->element;
		$document = $this->document();

		if ($element->ownerDocument !== $document) {
			$adopted = $document->adoptNode($element);
			assert($adopted instanceof Element);
			$element = $adopted;
		}

		// The same complex type may appear under different element names; the parent decides the tag and the
		// namespace — so an extension wrapper minted under a subclass name (and namespace) lands as the ISDOC
		// <Extensions> its parent expects.
		if ($element->localName !== $name || $element->namespaceURI !== $this->namespace) {
			$element->rename($this->namespace, $this->qualified($name));
		}

		$this->element->insertBefore($element, $this->position($name));
	}

	/** Creates an empty child element (in this node's namespace) in the correct XSD-sequence position. */
	private function insert(string $name): Element
	{
		$element = $this->document()->createElementNS($this->namespace, $this->qualified($name));
		$this->element->insertBefore($element, $this->position($name));

		return $element;
	}

	/** The element name qualified with this node's prefix, when one is declared. */
	private function qualified(string $name): string
	{
		return $this->prefix === null ? $name : "$this->prefix:$name";
	}

	/** The existing child element a new {@code $name} element must be inserted before, or null to append. */
	private function position(string $name): ?Element
	{
		$index = array_search($name, $this->order, true);

		if ($index === false) {
			return null;
		}

		foreach ($this->element->childNodes as $node) {
			if (!$node instanceof Element) {
				continue;
			}

			$at = array_search($node->localName, $this->order, true);

			if ($at !== false && $at > $index) {
				return $node;
			}
		}

		return null;
	}

	/** @return WeakMap<Element, Entity> */
	private static function views(): WeakMap
	{
		return self::$views ??= new WeakMap();
	}

	/** @return WeakMap<Element, Element> */
	private static function pendingReferences(): WeakMap
	{
		return self::$pendingReferences ??= new WeakMap();
	}

	/** @param class-string<BackedEnum> $enum */
	private static function enumIsInt(string $enum): bool
	{
		return self::$enumBackings[$enum] ??= new ReflectionEnum($enum)->getBackingType()?->getName() === 'int';
	}

	/**
	 * Whether a child element matches the given local name in this node's namespace. An ISDOC node also accepts
	 * an un-namespaced element (a file that omits the default namespace); a foreign-namespace node matches its
	 * exact URI only.
	 */
	private function matches(Element $node, string $name): bool
	{
		if ($node->localName !== $name) {
			return false;
		}

		return $node->namespaceURI === $this->namespace
			|| ($this->namespace === self::Namespace && $node->namespaceURI === null);
	}

	private static function directText(Element $element): ?string
	{
		$text  = '';
		$found = false;

		foreach ($element->childNodes as $node) {
			if ($node instanceof Text) {
				$text  .= $node->data;
				$found  = true;
			}
		}

		return $found ? $text : null;
	}

	private static function setElementText(Element $element, ?string $value): void
	{
		foreach (iterator_to_array($element->childNodes) as $node) {
			if ($node instanceof Text) {
				$element->removeChild($node);
			}
		}

		if ($value === null || $value === '') {
			return;
		}

		$document = $element->ownerDocument;
		assert($document !== null);
		$element->appendChild($document->createTextNode($value));
	}

}