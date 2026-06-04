<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Generator;

use LogicException;

/** A schema class to render. */
final class ClassModel
{

	public const KindEntity          = 'entity';
	public const KindCollection    = 'collection';
	public const KindSimpleContent = 'simpleContent';

	public string $name = '';
	public string $namespaceSuffix = 'Invoice';
	public bool $isRoot = false;
	public ?string $doc = null;
	public string $kind = self::KindEntity;
	public bool $unwrapped = false;
	public bool $isExtension = false;

	public ?string $itemElement = null;
	public ?string $itemType = null;

	/** @var list<MemberModel> */
	public array $members = [];

	/** @var list<MemberModel> */
	public array $constructor = [];

	public function hasMember(string $name): bool
	{
		return array_any($this->members, fn($member) => $member->name === $name);
	}

	public function isCollection(): bool
	{
		return $this->kind === self::KindCollection;
	}

}

/** A single member (property) of a schema class. */
final class MemberModel
{

	public const string KindScalar    = 'scalar';
	public const string KindAttribute = 'attribute';
	public const string KindComplex   = 'complex';
	public const string KindCollection = 'collection';
	public const string KindReference  = 'reference';
	public const string KindContent   = 'content';

	public ?string $kind = null;
	public string $name = '';
	public ?string $mapName = null;
	public string $phpType = 'string';
	public bool $nullable = false;
	public bool $unwrapped = false;
	public bool $isEnum = false;
	public ?string $access = null;
	public bool $isDecimal = false;
	public ?string $doc = null;
	public ?string $scope = null;

	/** @var list<string> */
	public array $restrictions = [];

}

/** A backed enum synthesized from an XSD enumeration restriction. */
final class EnumModel
{

	public string $name = '';
	public string $backing = 'int';
	public ?string $doc = null;

	/** @var array<string, int|string> case name => backing value, in XSD order */
	public array $cases = [];

	/** @var array<string, string> case name => doc comment (the XSD enumeration's description), if any */
	public array $caseDocs = [];

}

/** Renders a {@see ClassModel} to PHP source matching the house style; phpcbf normalises afterwards. */
final class Renderer
{

	/** @var array<string, true> */
	private array $imports = [];

	public function __construct(private readonly string $namespace)
	{
	}

	private string $currentNamespace = '';

	public function render(ClassModel $class): string
	{
		$this->imports = [];
		$this->currentNamespace = $this->namespace . ($class->namespaceSuffix === '' ? '' : '\\' . $class->namespaceSuffix);

		if ($class->isExtension) {
			$interfaces = ['Extension'];
			$this->use('Adawolfa\\ISDOC\\Schema\\Extension');
		} else {
			$interfaces = ['Entity'];
			$this->use('Adawolfa\\ISDOC\\Schema\\Entity');
		}

		if ($class->isCollection()) {
			$interfaces[] = 'IteratorAggregate';
			$interfaces[] = 'Countable';
			$this->use('IteratorAggregate');
			$this->use('Countable');
		}

		// Render the body first so every used symbol is collected before the import block is emitted.
		$body = $this->classBody($class);

		$namespace = $this->namespace . ($class->namespaceSuffix === '' ? '' : '\\' . $class->namespaceSuffix);

		$out  = "<?php declare(strict_types=1);\n\n";
		$out .= "namespace $namespace;\n\n";

		$imports = $this->renderImports();

		if ($imports !== '') {
			$out .= $imports . "\n\n";
		}

		$docLines = [];

		if ($class->doc !== null) {
			$docLines[] = $class->doc;
		}

		if ($class->isCollection()) {
			$itemType   = $class->itemType === 'string' ? 'string' : $this->qualify($class, $class->itemType ?? 'Entity');
			$docLines[] = '@implements IteratorAggregate<int, ' . $itemType . '>';
		}

		if (count($docLines) > 0) {
			$out .= "/**\n";
			foreach ($docLines as $line) {
				$out .= ' * ' . $line . "\n";
			}
			$out .= " */\n";
		}

		$out .= 'class ' . $class->name . ' implements ' . implode(', ', $interfaces) . "\n{\n\n";
		$out .= $body;
		$out .= '}';

		return $out;
	}

	/** Renders a backed enum synthesized from an XSD enumeration; phpcbf normalises afterwards. */
	public function renderEnum(EnumModel $enum): string
	{
		$namespace = $this->namespace . '\\Invoice';

		$out  = "<?php declare(strict_types=1);\n\n";
		$out .= "namespace $namespace;\n\n";

		if ($enum->doc !== null) {
			$out .= "/**\n * " . $enum->doc . "\n */\n";
		}

		$out .= 'enum ' . $enum->name . ': ' . $enum->backing . "\n{\n\n";

		$cases = [];

		foreach ($enum->cases as $case => $value) {
			$doc     = isset($enum->caseDocs[$case]) ? "\t/** " . $enum->caseDocs[$case] . " */\n" : '';
			$cases[] = $doc . "\tcase " . $case . ' = ' . $this->export($value) . ';';
		}

		$out .= implode("\n\n", $cases) . "\n\n}";

		return $out;
	}

	private function classBody(ClassModel $class): string
	{
		$parts = [];

		if (!$class->unwrapped && !$class->isExtension) {
			$this->use('Adawolfa\\ISDOC\\Schema\\Backing');
			$parts[] = "\tuse Backing;";
		}

		// Unwrapped sibling collections share the parent's element, so they keep the parent's child order
		// instead of applying their own — hence a bespoke node hook rather than the Backing trait. The element
		// tag and order are inferred from the class (see Node::entity()); no Element/Order constants are emitted.
		if ($class->unwrapped) {
			$this->use('Adawolfa\\ISDOC\\XML\\Node');
			$parts[] = "\tprivate ?Node \$isdocNode = null;\n\n"
				. "\tpublic Node \$node {\n"
				. "\t\tget => \$this->isdocNode ??= Node::entity(self::class);\n"
				. "\t\tset { \$this->isdocNode = \$value; }\n"
				. "\t}";
		}

		// An <Extensions> type binds its tag, child order AND namespace from the runtime class (static::), so a
		// user subclass that adds typed hooks and an #[XMLNamespace] is honoured; the parent renames the wrapper
		// back to the ISDOC <Extensions>. Hence a bespoke node hook rather than the self::-pinned Backing trait.
		if ($class->isExtension) {
			$this->use('Adawolfa\\ISDOC\\XML\\Node');
			$parts[] = "\tprivate ?Node \$isdocNode = null;\n\n"
				. "\tpublic Node \$node {\n"
				. "\t\tget => \$this->isdocNode ??= Node::entity(static::class);\n"
				. "\t\tset {\n"
				. "\t\t\t\$this->isdocNode = \$value\n"
				. "\t\t\t\t->withOrder(Node::orderFor(static::class))\n"
				. "\t\t\t\t->withNamespace(Node::namespaceFor(static::class), Node::prefixFor(static::class));\n"
				. "\t\t}\n"
				. "\t}";

			// A read binds <Extensions> to this generic base; as() re-views it as a typed extension subclass so a
			// custom block reads back without touching the $node escape hatch.
			$parts[] = "\t/**\n"
				. "\t * @template T of Extension\n"
				. "\t * @param class-string<T> \$class\n"
				. "\t * @return T\n"
				. "\t */\n"
				. "\tpublic function as(string \$class): Extension\n\t{\n"
				. "\t\treturn \$this->node->bind(\$class);\n\t}";
		}

		foreach ($class->members as $member) {
			$parts[] = $this->property($class, $member);
		}

		if (count($class->constructor) > 0) {
			$parts[] = $this->constructor($class);
		}

		if ($class->isCollection()) {
			$parts[] = $this->collectionMethods($class);
		}

		return implode("\n\n", $parts) . "\n\n";
	}

	private function property(ClassModel $class, MemberModel $member): string
	{
		if ($member->isEnum) {
			return $this->enumProperty($class, $member);
		}

		$type    = $this->phpType($class, $member);
		$declared = ($member->nullable ? '?' : '') . $type;

		return match ($member->kind) {
			MemberModel::KindScalar, MemberModel::KindAttribute => $this->scalarProperty($member, $declared),
			MemberModel::KindContent                            => $this->contentProperty(),
			MemberModel::KindComplex                            => $this->complexProperty($member, $declared, $type),
			MemberModel::KindCollection                         => $member->unwrapped
				? $this->unwrappedCollectionProperty($member, $type)
				: $this->collectionProperty($member, $declared, $type),
			MemberModel::KindReference                          => $this->referenceProperty($member, $type),
			default                                             => throw new LogicException('Unknown member kind.'),
		};
	}

	private function scalarProperty(MemberModel $member, string $declared): string
	{
		[$read, $write] = $this->accessMethods($member);
		$map            = $this->export($member->mapName);

		if ($member->nullable) {
			// getString() never fails, but the parsing readers (getInt/getBool/getDate/getNumber) throw on a present-but-malformed value.
			if ($read === 'getString') {
				$get = "\t\tget => \$this->node->$read($map);";
			} else {
				$this->use('Adawolfa\\ISDOC\\XML\\Exception');
				$get = "\t\t/** @throws Exception */\n\t\tget => \$this->node->$read($map);";
			}
		} else {
			$this->use('Adawolfa\\ISDOC\\XML\\Exception');
			$get = "\t\t/** @throws Exception */\n\t\tget => \$this->node->{$read}OrThrow($map);";
		}

		$set = "\t\tset {\n";

		foreach ($member->restrictions as $restriction) {
			$this->use('Adawolfa\\ISDOC\\Restriction');
			$set .= "\t\t\t$restriction\n";
		}

		$set .= "\t\t\t\$this->node->$write($map, \$value);\n\t\t}";

		return $this->doc($member) . "\tpublic $declared \$$member->name {\n$get\n$set\n\t}";
	}

	private function enumProperty(ClassModel $class, MemberModel $member): string
	{
		$type     = $this->qualify($class, $member->phpType);
		$declared = ($member->nullable ? '?' : '') . $type;
		$map      = $this->export($member->mapName);

		$this->use('Adawolfa\\ISDOC\\XML\\Exception');

		if ($member->nullable) {
			$get = "\t\t/** @throws Exception */\n\t\tget => \$this->node->getEnum($map, $type::class);";
		} else {
			$get = "\t\t/** @throws Exception */\n\t\tget => \$this->node->getEnumOrThrow($map, $type::class);";
		}

		$set = "\t\tset { \$this->node->setEnum($map, \$value); }";

		return $this->doc($member) . "\tpublic $declared \$$member->name {\n$get\n$set\n\t}";
	}

	private function contentProperty(): string
	{
		return "\tpublic ?string \$content {\n"
			. "\t\tget => \$this->node->text;\n"
			. "\t\tset { \$this->node->text = \$value; }\n"
			. "\t}";
	}

	private function complexProperty(MemberModel $member, string $declared, string $type): string
	{
		$map = $this->export($member->mapName);

		if ($member->nullable) {
			$get = "\t\tget => \$this->node->getChild($map, $type::class);";
		} else {
			$this->use('Adawolfa\\ISDOC\\XML\\Exception');
			$get = "\t\t/** @throws Exception */\n\t\tget => \$this->node->getChildOrThrow($map, $type::class);";
		}

		$set = "\t\tset { \$this->node->setChild($map, \$value); }";

		return $this->doc($member) . "\tpublic $declared \$$member->name {\n$get\n$set\n\t}";
	}

	private function collectionProperty(MemberModel $member, string $declared, string $type): string
	{
		$map = $this->export($member->mapName);

		if ($member->nullable) {
			$get = "\t\tget => \$this->node->getChild($map, $type::class);";
		} else {
			$get = "\t\tget => \$this->node->ensureChild($map, $type::class);";
		}

		$set = "\t\tset { \$this->node->setChild($map, \$value); }";

		return $this->doc($member) . "\tpublic $declared \$$member->name {\n$get\n$set\n\t}";
	}

	private function unwrappedCollectionProperty(MemberModel $member, string $type): string
	{
		$item = $this->export($member->mapName);

		$get = "\t\tget => count(\$this->node->getChildren($item)) > 0 ? \$this->node->view($type::class) : null;";
		$set = "\t\tset {\n"
			. "\t\t\t\$this->node->remove($item);\n"
			. "\t\t\tforeach (\$value ?? [] as \$item) {\n"
			. "\t\t\t\t\$this->node->addChild($item, \$item);\n"
			. "\t\t\t}\n"
			. "\t\t}";

		return $this->doc($member) . "\tpublic ?$type \$$member->name {\n$get\n$set\n\t}";
	}

	private function referenceProperty(MemberModel $member, string $type): string
	{
		$field = 'isdocReference' . ucfirst($member->name);

		$this->use('Adawolfa\\ISDOC\\XML\\Exception');

		$out  = "\tprivate ?$type \$$field = null;\n\n";
		$out .= "\tpublic $type \$$member->name {\n";
		$out .= "\t\t/** @throws Exception */\n";
		$out .= "\t\tget {\n";
		$out .= "\t\t\tif (\$this->$field !== null) {\n\t\t\t\treturn \$this->$field;\n\t\t\t}\n\n";
		$out .= "\t\t\t\$ref = \$this->node->getString('@ref');\n\n";
		$out .= "\t\t\treturn \$ref !== null\n";
		$out .= "\t\t\t\t? \$this->node->getReference($type::class, \$ref)\n";
		$out .= "\t\t\t\t: \$this->node->view($type::class);\n";
		$out .= "\t\t}\n";
		$out .= "\t\tset {\n";
		$out .= "\t\t\t\$this->$field = \$value;\n";
		$out .= "\t\t\t\$this->node->setReference(\$value);\n";
		$out .= "\t\t}\n";
		$out .= "\t}";

		return $this->doc($member) . $out;
	}

	private function constructor(ClassModel $class): string
	{
		$params = [];
		$body   = [];

		foreach ($class->constructor as $member) {
			$type     = $this->phpType($class, $member);
			$params[] = "\t\t" . ($member->nullable ? '?' : '') . "$type \$$member->name,";
			$body[]   = "\t\t\$this->$member->name = \$$member->name;";
		}

		return "\tpublic function __construct(\n" . implode("\n", $params) . "\n\t)\n\t{\n"
			. implode("\n", $body) . "\n\t}";
	}

	private function collectionMethods(ClassModel $class): string
	{
		$item    = $this->export($class->itemElement);
		$itemVar = $this->propertyName($class->itemElement ?? 'item');
		$this->use('Generator');

		if ($class->itemType === 'string') {
			$this->use('Adawolfa\\ISDOC\\XML\\Node');
			$getIterator = "\t/** @return Generator<int, string> */\n"
				. "\tpublic function getIterator(): Generator\n\t{\n"
				. "\t\tforeach (\$this->node->getChildren($item) as \$child) {\n"
				. "\t\t\tyield \$child->text ?? '';\n"
				. "\t\t}\n\t}";

			$add = "\tpublic function add(string \$$itemVar): self\n\t{\n"
				. "\t\t\$node = Node::create($item);\n"
				. "\t\t\$node->text = \$$itemVar;\n"
				. "\t\t\$this->node->addChild($item, \$node);\n\n"
				. "\t\treturn \$this;\n\t}";
		} else {
			$itemType = $this->qualify($class, $class->itemType ?? 'Entity');
			$getIterator = "\t/** @return Generator<int, $itemType> */\n"
				. "\tpublic function getIterator(): Generator\n\t{\n"
				. "\t\tyield from \$this->node->getChildren($item, $itemType::class);\n\t}";

			$add = "\tpublic function add($itemType \$$itemVar): self\n\t{\n"
				. "\t\t\$this->node->addChild($item, \$$itemVar);\n\n"
				. "\t\treturn \$this;\n\t}";
		}

		$count = "\tpublic function count(): int\n\t{\n"
			. "\t\treturn count(\$this->node->getChildren($item));\n\t}";

		return "$getIterator\n\n$add\n\n$count";
	}

	private function doc(MemberModel $member): string
	{
		return $member->doc === null ? '' : "\t/** $member->doc */\n";
	}

	/** @return array{string, string} */
	private function accessMethods(MemberModel $member): array
	{
		return match ($member->access) {
			'int'     => ['getInt', 'setInt'],
			'bool'    => ['getBool', 'setBool'],
			'date'    => ['getDate', 'setDate'],
			'decimal' => ['getNumber', 'setNumber'],
			default   => ['getString', 'setString'],
		};
	}

	private function phpType(ClassModel $class, MemberModel $member): string
	{
		if ($member->kind === MemberModel::KindContent) {
			return 'string';
		}

		return $this->qualify($class, $member->phpType);
	}

	private function qualify(ClassModel $class, string $type): string
	{
		if (in_array($type, ['string', 'int', 'bool'], true)) {
			return $type;
		}

		if ($type === 'DateTimeInterface') {
			$this->use('DateTimeInterface');
			return 'DateTimeInterface';
		}

		if ($type === 'Number') {
			$this->use('BcMath\\Number');
			return 'Number';
		}

		return $class->isRoot ? "Invoice\\$type" : $type;
	}

	private function use(string $fqn): void
	{
		$this->imports[$fqn] = true;
	}

	private function renderImports(): string
	{
		if (count($this->imports) === 0) {
			return '';
		}

		$imports = [];

		foreach (array_keys($this->imports) as $fqn) {
			// A symbol in the file's own namespace resolves unqualified; importing it is prohibited.
			$namespace = strrpos($fqn, '\\') === false ? '' : substr($fqn, 0, strrpos($fqn, '\\'));
			if ($namespace !== $this->currentNamespace) {
				$imports[] = $fqn;
			}
		}

		sort($imports);

		return implode("\n", array_map(static fn(string $fqn): string => "use $fqn;", $imports));
	}

	private function propertyName(string $name): string
	{
		if (strtoupper($name) === $name) {
			return strtolower($name);
		}

		if (preg_match('~^([A-Z]+)([A-Z][a-z].*)~', $name, $matches) === 1) {
			return strtolower($matches[1]) . $matches[2];
		}

		return lcfirst($name);
	}

	private function export(int|string|null $value): string
	{
		if (is_int($value)) {
			return (string) $value;
		}

		return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $value) . "'";
	}

}
