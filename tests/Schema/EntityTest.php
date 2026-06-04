<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC\Schema;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use Adawolfa\ISDOC\XML\Node;
use Countable;
use Dom\Element as DomElement;
use Dom\XMLDocument;
use Generator;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;

// Fixtures mirror the generated one-class-per-file shape; they are inlined here only to keep the proof local.
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

/** Simple-content leaf reused under two different tags ({@code Note} and {@code VATNote}). */
final class Note implements Entity
{

	use Backing;

	public ?string $content {
		get => $this->node->text;
		set { $this->node->text = $value; }
	}

	public ?string $lang {
		get => $this->node->getString('@languageID');
		set { $this->node->setString('@languageID', $value); }
	}

	public function __construct(?string $content = null)
	{
		$this->content = $content;
	}

}

final class Leaf implements Entity
{

	use Backing;

	public string $code {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('Code');
		set { $this->node->setString('Code', $value); }
	}

	public function __construct(string $code)
	{
		$this->code = $code;
	}

}

final class Child implements Entity
{

	use Backing;

	public string $name {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('Name');
		set { $this->node->setString('Name', $value); }
	}

	public Leaf $leaf {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('Leaf', Leaf::class);
		set { $this->node->setChild('Leaf', $value->node); }
	}

	public function __construct(string $name, Leaf $leaf)
	{
		$this->name = $name;
		$this->leaf = $leaf;
	}

}

final class Item implements Entity
{

	use Backing;

	public string $id {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('ID');
		set { $this->node->setString('ID', $value); }
	}

	public function __construct(string $id)
	{
		$this->id = $id;
	}

}

/** @implements IteratorAggregate<int, Item> */
final class Items implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, Item> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('Item', Item::class);
	}

	public function add(Item $item): self
	{
		$this->node->addChild('Item', $item->node);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('Item'));
	}

}

final class Root implements Entity
{

	public Node $node {
		get => $this->node ??= Node::create('Invoice', Node::orderFor(self::class));
		set { $this->node = $value->withOrder(Node::orderFor(self::class)); }
	}

	public int $documentType {
		/** @throws Exception */
		get => $this->node->getIntOrThrow('DocumentType');
		set { $this->node->setInt('DocumentType', $value); }
	}

	public string $id {
		get => $this->node->getString('ID') ?? throw Exception::missingValue($this->node->getPath('ID'));
		set { $this->node->setString('ID', $value); }
	}

	public ?string $subType {
		get => $this->node->getString('SubType');
		set { $this->node->setString('SubType', $value); }
	}

	public Note $note {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('Note', Note::class);
		set { $this->node->setChild('Note', $value->node); }
	}

	public ?Note $vatNote {
		get => $this->node->getChild('VATNote', Note::class);
		set { $this->node->setChild('VATNote', $value?->node); }
	}

	public Child $child {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('Child', Child::class);
		set { $this->node->setChild('Child', $value->node); }
	}

	public Items $items {
		get => $this->node->ensureChild('Items', Items::class);
		set { $this->node->setChild('Items', $value->node); }
	}

	public function __construct(int $documentType, string $id, Note $note, Child $child)
	{
		$this->documentType = $documentType;
		$this->id           = $id;
		$this->note         = $note;
		$this->child        = $child;
	}

}

final class EntityTest extends TestCase
{

	private function serialize(Entity $entity): string
	{
		$document = $entity->node->dom->ownerDocument;
		self::assertInstanceOf(XMLDocument::class, $document);
		$document->formatOutput = true;
		$xml = $document->saveXml($entity->node->dom);
		self::assertIsString($xml);

		return $xml;
	}

	private function build(): Root
	{
		$root = new Root(1, '12345', new Note('hello'), new Child('child name', new Leaf('CODE')));
		$root->note->lang = 'en';
		$root->vatNote = new Note('vat note'); // reused Note type, attached under <VATNote>
		$root->items->add(new Item('1'))->add(new Item('2'));

		return $root;
	}

	public function testPublicNodeEscapeHatch(): void
	{
		$root = new Root(1, '12345', new Note('hello'), new Child('n', new Leaf('C')));

		// $node is public: callers can reach unmapped content directly
		$root->node->setString('@nonStandardAttribute', 'x');
		self::assertSame('x', $root->node->getString('@nonStandardAttribute'));

		// the element tag is inferred from the class basename — no Element constant
		self::assertSame('Invoice', $root->node->dom->localName);
	}

	public function testWriteProducesSchemaOrderedTreeWithRename(): void
	{
		$xml = $this->serialize($this->build());

		self::assertLessThan(strpos($xml, '<ID>'), strpos($xml, '<DocumentType>'));
		self::assertLessThan(strpos($xml, '<Note'), strpos($xml, '<ID>'));
		self::assertLessThan(strpos($xml, '<VATNote>'), strpos($xml, '<Note'));
		self::assertLessThan(strpos($xml, '<Child>'), strpos($xml, '<VATNote>'));
		self::assertLessThan(strpos($xml, '<Items>'), strpos($xml, '<Child>'));

		// rename-on-attach: a Note instance attached as <VATNote>
		self::assertStringContainsString('<VATNote>vat note</VATNote>', $xml);
		self::assertStringContainsString('<Note languageID="en">hello</Note>', $xml);

		self::assertStringContainsString('<Code>CODE</Code>', $xml);
		self::assertLessThan(strpos($xml, '<ID>2</ID>'), strpos($xml, '<ID>1</ID>'));
		self::assertSame(1, substr_count($xml, 'xmlns="' . Node::Namespace . '"'));
	}

	public function testReadFromParsedDocument(): void
	{
		$xml = <<<'XML'
			<Invoice xmlns="http://isdoc.cz/namespace/2013">
				<DocumentType>1</DocumentType>
				<ID>12345</ID>
				<Note languageID="cs">ahoj</Note>
				<Child><Name>n</Name><Leaf><Code>C</Code></Leaf></Child>
				<Items><Item><ID>a</ID></Item><Item><ID>b</ID></Item></Items>
			</Invoice>
			XML;

		$root = Node::wrap($this->parse($xml))->bind(Root::class);

		self::assertSame(1, $root->documentType);
		self::assertSame('12345', $root->id);
		self::assertNull($root->subType);
		self::assertSame('ahoj', $root->note->content);
		self::assertSame('cs', $root->note->lang);
		self::assertNull($root->vatNote);
		self::assertSame('n', $root->child->name);
		self::assertSame('C', $root->child->leaf->code);
		self::assertCount(2, $root->items);

		$ids = array_map(static fn(Item $item): string => $item->id, iterator_to_array($root->items));
		self::assertSame(['a', 'b'], $ids);
	}

	public function testMissingRequiredThrowsLazilyAtAccessOnly(): void
	{
		$xml = <<<'XML'
			<Invoice xmlns="http://isdoc.cz/namespace/2013">
				<DocumentType>7</DocumentType>
				<Child><Name>n</Name><Leaf><Code>C</Code></Leaf></Child>
			</Invoice>
			XML;

		$root = Node::wrap($this->parse($xml))->bind(Root::class);

		self::assertSame(7, $root->documentType); // unrelated field reads fine

		$this->expectException(Exception::class);
		$this->expectExceptionMessage("Missing required value 'Invoice/ID'.");
		$root->id; // only touching the bad field raises @phpstan-ignore expr.resultUnused
	}

	public function testRoundTrip(): void
	{
		$root = Node::wrap($this->parse($this->serialize($this->build())))->bind(Root::class);

		self::assertSame(1, $root->documentType);
		self::assertSame('12345', $root->id);
		self::assertSame('hello', $root->note->content);
		self::assertSame('en', $root->note->lang);
		self::assertSame('vat note', $root->vatNote?->content);
		self::assertSame('child name', $root->child->name);
		self::assertSame('CODE', $root->child->leaf->code);
		self::assertCount(2, $root->items);
	}

	public function testMutatingChildAfterAttachReachesTree(): void
	{
		$root = $this->build();
		$root->child->leaf->code = 'CHANGED';

		self::assertStringContainsString('<Code>CHANGED</Code>', $this->serialize($root));
	}

	private function parse(string $xml): DomElement
	{
		$document = XMLDocument::createFromString($xml, LIBXML_NOERROR);
		$root     = $document->documentElement;
		self::assertInstanceOf(DomElement::class, $root);

		return $root;
	}

}