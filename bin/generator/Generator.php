<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Generator;

use Adawolfa\ISDOC\Invoice;
use GoetasWebservices\XML\XSDReader\Schema\Attribute\Attribute;
use GoetasWebservices\XML\XSDReader\Schema\Attribute\AttributeItem;
use GoetasWebservices\XML\XSDReader\Schema\Attribute\AttributeSingle;
use GoetasWebservices\XML\XSDReader\Schema\Attribute\Group as AttributeGroup;
use GoetasWebservices\XML\XSDReader\Schema\Element\Choice;
use GoetasWebservices\XML\XSDReader\Schema\Element\Element;
use GoetasWebservices\XML\XSDReader\Schema\Element\ElementDef;
use GoetasWebservices\XML\XSDReader\Schema\Element\ElementItem;
use GoetasWebservices\XML\XSDReader\Schema\Element\Group as ElementGroup;
use GoetasWebservices\XML\XSDReader\Schema\Element\Sequence;
use GoetasWebservices\XML\XSDReader\Schema\Inheritance\Restriction;
use GoetasWebservices\XML\XSDReader\Schema\Schema;
use GoetasWebservices\XML\XSDReader\Schema\Type\BaseComplexType;
use GoetasWebservices\XML\XSDReader\Schema\Type\ComplexType;
use GoetasWebservices\XML\XSDReader\Schema\Type\ComplexTypeSimpleContent;
use GoetasWebservices\XML\XSDReader\Schema\Type\SimpleType;
use GoetasWebservices\XML\XSDReader\Schema\Type\Type;
use RuntimeException;

require __DIR__ . '/Model.php';

/**
 * Generates the lazy DOM-backed schema entity classes from the ISDOC XSD.
 *
 * Each complex type becomes a final class implementing {@see \Adawolfa\ISDOC\Schema\Entity} via the
 * {@see \Adawolfa\ISDOC\Schema\Backing} trait: every member is a property hook delegating to the backing
 * {@see \Adawolfa\ISDOC\XML\Node}. The output is final — there are no post-generation patches; phpcbf
 * normalises whitespace afterwards.
 */
final class Generator
{

	/**
	 * Abbreviations kept fully upper-case in generated PascalCase identifiers (enum names and cases), so the
	 * house rule — never lower-case an abbreviation — survives regeneration. Matched whole-word, case-insensitively.
	 *
	 * @var list<string>
	 */
	private const array Abbreviations = ['ISDOC', 'ISDOCX', 'PDF', 'VAT'];

	private const SimpleTypeMap = [
		'date'          => ['DateTimeInterface', 'date'],
		'decimal'       => ['Number', 'decimal'],
		'integer'       => ['int', 'int'],
		'string'        => ['string', 'string'],
		'IDType'        => ['string', 'string'],
		'token'         => ['string', 'string'],
		'boolean'       => ['bool', 'bool'],
		'anySimpleType' => ['string', 'string'],
		'anyURI'        => ['?string', 'string'],
		'BooleanType'   => ['bool', 'bool'],
	];

	/** @var array<string, ClassModel> */
	private array $classes = [];

	/** @var array<string, EnumModel> */
	private array $enums = [];

	public function __construct(
		private readonly string $folder,
		private readonly string $namespace,
	)
	{
	}

	public function make(Schema $schema, string $xsdVersion): void
	{
		$this->checkVersion($xsdVersion);

		$invoice = $schema->getElement('Invoice');

		if (!$invoice instanceof ElementDef) {
			throw new RuntimeException('Invoice must be an element definition.');
		}

		$invoiceType = $invoice->getType();

		if (!$invoiceType instanceof ComplexType) {
			throw new RuntimeException('Invoice type must be a complex type.');
		}

		$this->complexType($invoiceType, 'Invoice', true, $invoice->getDoc());

		foreach ($schema->getTypes() as $type) {
			if ($type instanceof BaseComplexType) {
				$this->complexType($type, $this->typeClassName($type), false);
			}
		}
	}

	/**
	 * The hand-written {@see Invoice::Version} default is not generated from the XSD, so it silently drifts when
	 * the schema is bumped. Fail generation loudly when it no longer matches the XSD's declared version, forcing
	 * src/Invoice.php to be updated in the same change.
	 */
	private function checkVersion(string $xsdVersion): void
	{
		if ($xsdVersion !== Invoice::Version) {
			throw new RuntimeException(sprintf(
				'ISDOC version mismatch: the XSD declares "%s" but %s::Version is "%s" — update src/Invoice.php.',
				$xsdVersion,
				Invoice::class,
				Invoice::Version,
			));
		}
	}

	public function write(): void
	{
		foreach ($this->classes as $class) {

			$path = $this->folder . '/'
				. ($class->namespaceSuffix === '' ? '' : str_replace('\\', '/', $class->namespaceSuffix) . '/')
				. $class->name . '.php';

			@mkdir(dirname($path), 0777, true);
			file_put_contents($path, new Renderer($this->namespace)->render($class));

		}

		foreach ($this->enums as $enum) {

			$path = $this->folder . '/Invoice/' . $enum->name . '.php';

			@mkdir(dirname($path), 0777, true);
			file_put_contents($path, new Renderer($this->namespace)->renderEnum($enum));

		}
	}

	private function complexType(BaseComplexType $type, string $name, bool $isRoot, ?string $doc = null): void
	{
		if (isset($this->classes[$name])) {
			return;
		}

		// <Extensions> (xs:any) is generated as a bare, subclassable carrier rather than skipped, so it takes a
		// place in the inferred child order (correct XSD position) and gives custom extensions a typed home.
		if ($name === 'Extensions') {
			$this->extensionType($type, $doc);
			return;
		}

		$class                 = new ClassModel();
		$class->name           = $name;
		$class->namespaceSuffix = $isRoot ? '' : 'Invoice';
		$class->isRoot         = $isRoot;
		$class->doc            = $this->formatDoc($doc ?? $type->getDoc());

		$this->classes[$name] = $class; // register early so recursion/order is stable

		if ($type instanceof ComplexTypeSimpleContent) {
			$class->kind = ClassModel::KindSimpleContent;
			$class->members[] = $this->simpleContentMember();
			$this->attributes($class, $type);
			$this->finishClass($class);
			return;
		}

		if ($type instanceof ComplexType && $this->isCollection($type)) {
			$this->collectionType($class, $type);
			$this->attributes($class, $type);
			$this->finishClass($class);
			return;
		}

		$class->kind = ClassModel::KindEntity;

		if ($type instanceof ComplexType) {
			$this->entityElements($class, $type);
		}

		$this->attributes($class, $type);
		$this->finishClass($class);
	}

	/**
	 * The {@code <Extensions>} wrapper: a non-final entity with no typed members (its content is xs:any). It is
	 * subclassed by user code to add typed, namespaced hooks; the {@see \Adawolfa\ISDOC\Schema\Extension} marker
	 * (and a bespoke namespace-aware node hook) keeps the namespace machinery scoped to extensions alone.
	 */
	private function extensionType(BaseComplexType $type, ?string $doc): void
	{
		$class                  = new ClassModel();
		$class->name            = 'Extensions';
		$class->namespaceSuffix = 'Invoice';
		$class->isExtension     = true;
		$class->doc             = $this->formatDoc($doc ?? $type->getDoc());

		$this->classes['Extensions'] = $class;
	}

	private function isCollection(ComplexType $type): bool
	{
		$elements = $type->getElements();
		$first    = $elements[0] ?? null;

		return $first instanceof Element && $first->getMax() !== 1;
	}

	private function collectionType(ClassModel $class, ComplexType $type): void
	{
		$class->kind = ClassModel::KindCollection;

		$elements = $type->getElements();
		$first    = $elements[0];
		assert($first instanceof Element);

		$class->itemElement = $first->getName();
		$class->itemType    = $this->elementPhpType($first);

		for ($i = 1; isset($elements[$i]); $i++) {
			$this->element($class, $elements[$i]);
		}
	}

	private function entityElements(ClassModel $class, ComplexType $type): void
	{
		$referenced = null;

		if ($type->getName() !== null && preg_match('~LineReferenceType$~', $type->getName()) === 1) {

			$referencedName = str_replace('Line', '', $type->getName());
			$referenced     = $type->getSchema()->getType($referencedName);

			if (!$referenced instanceof ComplexType) {
				throw new RuntimeException('Referenced type must be a complex type.');
			}

			$member            = new MemberModel();
			$member->kind      = MemberModel::KindReference;
			$member->name      = $this->propertyName($referencedName);
			$member->phpType   = $this->typeClassName($referenced);
			$member->scope     = null;
			$member->nullable  = false;
			$class->members[]  = $member;

		}

		foreach ($type->getElements() as $element) {

			if ($referenced !== null && $element instanceof Element) {
				foreach ($referenced->getElements() as $referencedElement) {
					if ($referencedElement instanceof Element && $referencedElement->getName() === $element->getName()) {
						continue 2;
					}
				}
			}

			$this->element($class, $element);

		}
	}

	private function element(ClassModel $class, ElementItem $element, ?bool $forceNullable = null): void
	{
		if ($element instanceof Choice || $element instanceof Sequence || $element instanceof ElementGroup) {
			foreach ($element->getElements() as $child) {
				$this->element($class, $child, true);
			}
			return;
		}

		if (!$element instanceof Element) {
			return; // xs:any and friends are reachable through the public $node escape hatch
		}

		$propertyName = $this->propertyName($element->getName());

		if ($class->hasMember($propertyName)) {
			return;
		}

		// A repeated element nested in an entity sequence (e.g. PartyTaxScheme) is an unwrapped sibling collection.
		if ($element->getMax() !== 1) {
			$this->inlineCollection($class, $element);
			return;
		}

		$type = $element->getType();

		$member          = new MemberModel();
		$member->name    = $propertyName;
		$member->mapName = $element->getName();
		$member->doc     = $this->formatDoc($element->getDoc());

		$nullable = $forceNullable
			?? ($element->getMin() === 0 || in_array($propertyName, ['subDocumentType', 'subDocumentTypeOrigin'], true));

		if ($type instanceof ComplexTypeSimpleContent || $type instanceof ComplexType) {
			$member->kind     = $this->isCollectionType($type) ? MemberModel::KindCollection : MemberModel::KindComplex;
			$member->phpType  = $this->typeClassName($type);
			$member->nullable = $nullable;
		} elseif ($type instanceof SimpleType) {
			$this->simpleMember($member, $type, $nullable);
		} else {
			throw new RuntimeException('Unsupported element type.');
		}

		$class->members[] = $member;
	}

	private function inlineCollection(ClassModel $class, Element $element): void
	{
		$itemType         = $element->getType();
		$collectionName   = $element->getName() . 's';
		$propertyName     = $this->propertyName($collectionName);

		if (!$itemType instanceof ComplexType && !$itemType instanceof ComplexTypeSimpleContent) {
			throw new RuntimeException('Unwrapped collection item must be a complex type.');
		}

		$itemClass = $this->typeClassName($itemType);

		// Synthesize the collection class once.
		if (!isset($this->classes[$collectionName])) {
			$collection                  = new ClassModel();
			$collection->name            = $collectionName;
			$collection->namespaceSuffix = 'Invoice';
			$collection->kind            = ClassModel::KindCollection;
			$collection->unwrapped       = true;
			$collection->itemElement     = $element->getName();
			$collection->itemType        = $itemClass;
			$collection->doc             = $this->formatDoc($element->getDoc());
			$this->classes[$collectionName] = $collection;
			$this->finishClass($collection);
		}

		$member            = new MemberModel();
		$member->kind      = MemberModel::KindCollection;
		$member->unwrapped = true;
		$member->name      = $propertyName;
		$member->mapName   = $element->getName();
		$member->phpType   = $collectionName;
		$member->nullable  = true;
		$member->doc       = $this->formatDoc($element->getDoc());
		$class->members[]  = $member;
	}

	private function attributes(ClassModel $class, BaseComplexType $type): void
	{
		foreach ($type->getAttributes() as $attribute) {

			if ($attribute instanceof AttributeGroup) {
				foreach ($attribute->getAttributes() as $groupAttribute) {
					$this->attribute($class, $groupAttribute);
				}
			} else {
				$this->attribute($class, $attribute);
			}

		}
	}

	private function attribute(ClassModel $class, AttributeItem $attribute): void
	{
		if (!$attribute instanceof Attribute) {
			throw new RuntimeException('Unsupported attribute kind.');
		}

		$name = $attribute->getName();

		if ($name === 'ref' || $name === 'id') {
			return;
		}

		$propertyName = $this->propertyName($name);

		if ($class->hasMember($propertyName)) {
			return;
		}

		$type = $attribute->getType();

		if (!$type instanceof SimpleType) {
			throw new RuntimeException('Unsupported attribute type.');
		}

		$member          = new MemberModel();
		$member->kind    = MemberModel::KindAttribute;
		$member->name    = $propertyName;
		$member->mapName = '@' . $name;
		$member->doc     = $this->formatDoc($attribute->getDoc());

		$this->simpleMember($member, $type, $attribute->getUse() === AttributeSingle::USE_OPTIONAL);

		$class->members[] = $member;
	}

	private function simpleContentMember(): MemberModel
	{
		$member          = new MemberModel();
		$member->kind    = MemberModel::KindContent;
		$member->name    = 'content';
		$member->phpType = 'string';
		$member->nullable = true;

		return $member;
	}

	private function simpleMember(MemberModel $member, SimpleType $type, bool $nullable): void
	{
		$type = $this->resolveUnion($type);

		$restriction = $type->getRestriction();

		if ($restriction === null || $restriction->getBase() === null) {
			throw new RuntimeException('Simple type without restriction.');
		}

		$base = $restriction->getBase()->getName();
		$map  = self::SimpleTypeMap[$base] ?? null;

		if ($map === null) {
			throw new RuntimeException("Unknown simple type '$base'.");
		}

		[$phpType, $access] = $map;

		if (str_starts_with($phpType, '?')) {
			$nullable = true;
			$phpType  = substr($phpType, 1);
		}

		$member->kind ??= MemberModel::KindScalar;
		$member->phpType  = $phpType;
		$member->access   = $access;
		$member->nullable = $nullable;
		$member->isDecimal = $access === 'decimal';

		$this->restrictions($member, $restriction);
	}

	private function resolveUnion(SimpleType $type): SimpleType
	{
		if (count($type->getUnions()) > 0) {

			foreach ($type->getUnions() as $union) {
				$restriction = $union->getRestriction();
				if ($restriction?->getBase()?->getName() !== null
					&& isset(self::SimpleTypeMap[$restriction->getBase()->getName()])) {
					return $union;
				}
			}

			throw new RuntimeException('Unsupported union type.');

		}

		return $type;
	}

	private function restrictions(MemberModel $member, Restriction $restriction): void
	{
		// A decimal is exposed and accepted as a BcMath\Number, which is always well-formed, so it needs no
		// set-hook validation (the former Restriction::decimal() check guarded raw strings, which are gone).

		foreach ($restriction->getChecks() as $check => $parameters) {

			switch ($check) {

				case 'maxLength':
					$member->restrictions[] = sprintf('Restriction::maxLength($value, %d);', $parameters[0]['value']);
					break;

				case 'length':
					$member->restrictions[] = sprintf('Restriction::length($value, %d);', $parameters[0]['value']);
					break;

				case 'pattern':
					if ($member->phpType === 'bool') {
						break;
					}
					$member->restrictions[] = sprintf('Restriction::pattern($value, %s);', var_export($parameters[0]['value'], true));
					break;

				case 'enumeration':
					$this->enumeration($member, $parameters);
					break;

			}

		}

		// The enum type itself enforces the value whitelist, so no scalar restrictions are emitted for it.
		if ($member->isEnum) {
			$member->restrictions = [];
		}
	}

	/**
	 * Synthesizes a backed enum from an XSD enumeration: the member becomes enum-typed and each option turns
	 * into an enum case.
	 *
	 * @param list<array{value: string, doc: string}> $parameters
	 */
	private function enumeration(MemberModel $member, array $parameters): void
	{
		$prefix   = strtoupper((string) preg_replace('~[A-Z]~', '_$0', $member->name)) . '_';
		$enumName = $this->applyAbbreviations(ucfirst($member->name));

		$enum = $this->enums[$enumName] ?? null;

		if ($enum === null) {
			$enum          = new EnumModel();
			$enum->name    = $enumName;
			$enum->backing = $member->phpType === 'int' ? 'int' : 'string';
			$enum->doc     = $member->doc;
			$this->enums[$enumName] = $enum;
		}

		foreach ($parameters as $option) {

			$label = substr($option['doc'], strpos($option['doc'], "\n") + 1);
			$name  = $prefix . strtoupper(strtr(str_replace(' ', '_', $label), ['(' => '', ')' => '']));
			$case  = substr($this->pascalConstant($name), strlen($enumName));

			$enum->cases[$case] = $member->phpType === 'int' ? (int) $option['value'] : $option['value'];

			// The XSD documents each enumeration option (cs + en); keep the English line as the case doc comment,
			// formatted exactly like every other generated doc (see formatDoc()).
			$doc = $this->formatDoc($option['doc']);

			if ($doc !== null) {
				$enum->caseDocs[$case] = $doc;
			}

		}

		$member->isEnum  = true;
		$member->phpType = $enumName;
	}

	/** Converts an UPPER_SNAKE_CASE constant name into its PascalCase canonical counterpart. */
	private function pascalConstant(string $name): string
	{
		return $this->applyAbbreviations(str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $name)))));
	}

	/**
	 * Re-uppercases any whole word of a PascalCase identifier that is a known abbreviation — the house rule
	 * never lower-cases an abbreviation (e.g. VAT, not Vat).
	 */
	private function applyAbbreviations(string $pascal): string
	{
		return (string) preg_replace_callback(
			'~[A-Z][a-z0-9]*~',
			static fn(array $m): string => in_array(strtoupper($m[0]), self::Abbreviations, true) ? strtoupper($m[0]) : $m[0],
			$pascal,
		);
	}

	private function finishClass(ClassModel $class): void
	{
		// The customer-party xs:choice lists AccountingCustomerParty first, but a simplified tax document
		// (the AnonymousCustomerParty + optional AccountingCustomerParty branch) must emit Anonymous first.
		// Member declaration order is what the runtime infers the child-element order from (see Node::orderFor()),
		// so this reorder is the single place that fixes the sequence — there is no Order constant any more.
		$this->ensureMemberOrder($class, 'anonymousCustomerParty', 'accountingCustomerParty');

		foreach ($class->members as $member) {
			if (!$member->nullable && $member->kind !== MemberModel::KindContent) {
				$class->constructor[] = $member;
			}
		}
	}

	private function isCollectionType(Type $type): bool
	{
		return $type instanceof ComplexType && $this->isCollection($type);
	}

	/** Moves the {@code $before} member directly ahead of {@code $after} when both are present and out of order. */
	private function ensureMemberOrder(ClassModel $class, string $before, string $after): void
	{
		$beforeIndex = $afterIndex = null;

		foreach ($class->members as $index => $member) {
			if ($member->name === $before) {
				$beforeIndex = $index;
			} elseif ($member->name === $after) {
				$afterIndex = $index;
			}
		}

		if ($beforeIndex === null || $afterIndex === null || $beforeIndex < $afterIndex) {
			return;
		}

		$moved = $class->members[$beforeIndex];
		unset($class->members[$beforeIndex]);
		array_splice($class->members, $afterIndex, 0, [$moved]);
		$class->members = array_values($class->members);
	}

	private function elementPhpType(Element $element): string
	{
		$type = $element->getType();

		if ($type instanceof SimpleType) {
			$resolved    = $this->resolveUnion($type);
			$restriction = $resolved->getRestriction();
			$base        = $restriction?->getBase()?->getName();
			return self::SimpleTypeMap[$base][0] ?? 'string';
		}

		if ($type instanceof ComplexType || $type instanceof ComplexTypeSimpleContent) {
			return $this->typeClassName($type);
		}

		throw new RuntimeException('Unsupported collection item type.');
	}

	private function typeClassName(BaseComplexType|Type $type): string
	{
		$name = $type->getName() ?? throw new RuntimeException('Anonymous type.');

		return (string) preg_replace('~(Reference)?Type$~', '', $name);
	}

	private function propertyName(string $name): string
	{
		if ($name !== 'Reference') {
			$name = (string) preg_replace('~Reference$~', '', $name);
		}

		if ($name !== 'ReferenceType') {
			$name = (string) preg_replace('~ReferenceType$~', '', $name);
		}

		if (strtoupper($name) === $name) {
			return strtolower($name);
		}

		if (preg_match('~^([A-Z]+)([A-Z][a-z].*)~', $name, $matches) === 1) {
			return strtolower($matches[1]) . $matches[2];
		}

		return lcfirst($name);
	}

	private function formatDoc(?string $doc): ?string
	{
		if ($doc === null) {
			return null;
		}

		$doc = str_replace(["\r\n", "\r"], "\n", $doc);

		// The XSD carries both a Czech and an English documentation line; prefer the English (last) one.
		$summary = null;

		foreach (explode("\n", $doc) as $line) {
			$line = trim($line);
			if ($line !== '') {
				$summary = $line;
			}
		}

		return $summary === null ? null : rtrim($summary, '.') . '.';
	}

}
