<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC\XML;

use Adawolfa\ISDOC\Schema\Invoice\BatchOrSerialNumber;
use Adawolfa\ISDOC\Schema\Invoice\VATCalculationMethod;
use Adawolfa\ISDOC\XML\Exception;
use Adawolfa\ISDOC\XML\Node;
use DateTimeImmutable;
use Dom\Element;
use Dom\XMLDocument;
use PHPUnit\Framework\TestCase;

final class NodeTest extends TestCase
{

	/** @param list<string> $order */
	private function parse(string $xml, array $order = []): Node
	{
		$document = XMLDocument::createFromString($xml, LIBXML_NOERROR);
		$root     = $document->documentElement;
		self::assertInstanceOf(Element::class, $root);

		return Node::wrap($root, $order);
	}

	private function serialize(Node $node): string
	{
		$document = $node->dom->ownerDocument;
		self::assertInstanceOf(XMLDocument::class, $document);
		$document->formatOutput = true;
		$xml = $document->saveXml($node->dom);
		self::assertIsString($xml);

		return $xml;
	}

	public function testReadStringElementAndAttribute(): void
	{
		$node = $this->parse(
			'<Invoice xmlns="http://isdoc.cz/namespace/2013" version="6.0.2"><ID>12345</ID></Invoice>',
		);

		self::assertSame('12345', $node->getString('ID'));
		self::assertSame('6.0.2', $node->getString('@version'));
		self::assertNull($node->getString('Missing'));
		self::assertNull($node->getString('@missing'));
		self::assertTrue($node->has('ID'));
		self::assertTrue($node->has('@version'));
		self::assertFalse($node->has('Missing'));
		self::assertFalse($node->has('@missing'));
	}

	public function testReadToleratesPrefixedNamespaceAndForeignElements(): void
	{
		$node = $this->parse(<<<'XML'
			<i:Invoice xmlns:i="http://isdoc.cz/namespace/2013" xmlns:x="urn:other">
				<i:ID>42</i:ID>
				<x:ID>foreign</x:ID>
			</i:Invoice>
			XML);

		self::assertSame('42', $node->getString('ID'));
		self::assertCount(1, $node->getChildren('ID'));
	}

	/**
	 * @throws Exception
	 */
	public function testReadInt(): void
	{
		$node = $this->parse(
			'<Invoice xmlns="http://isdoc.cz/namespace/2013"><A>  10  </A><B>-3</B></Invoice>',
		);

		self::assertSame(10, $node->getInt('A'));
		self::assertSame(-3, $node->getInt('B'));
		self::assertNull($node->getInt('Missing'));
	}

	/**
	 * @throws Exception
	 */
	public function testReadBoolVariants(): void
	{
		$node = $this->parse(<<<'XML'
			<Invoice xmlns="http://isdoc.cz/namespace/2013">
				<T1>true</T1><T2>1</T2><T3> TRUE </T3>
				<F1>false</F1><F2>0</F2>
			</Invoice>
			XML);

		self::assertTrue($node->getBool('T1'));
		self::assertTrue($node->getBool('T2'));
		self::assertTrue($node->getBool('T3'));
		self::assertFalse($node->getBool('F1'));
		self::assertFalse($node->getBool('F2'));
		self::assertNull($node->getBool('Missing'));
	}

	/**
	 * @throws Exception
	 */
	public function testReadDate(): void
	{
		$node = $this->parse(
			'<Invoice xmlns="http://isdoc.cz/namespace/2013"><D>2021-08-16</D><E></E></Invoice>',
		);

		$date = $node->getDate('D');
		self::assertNotNull($date);
		self::assertSame('2021-08-16 00:00:00', $date->format('Y-m-d H:i:s'));
		self::assertNull($node->getDate('E'));
		self::assertNull($node->getDate('Missing'));
	}

	public function testReadText(): void
	{
		$node = $this->parse(
			'<Note xmlns="http://isdoc.cz/namespace/2013" languageID="en">Hello</Note>',
		);

		self::assertSame('Hello', $node->text);
		self::assertSame('en', $node->getString('@languageID'));
	}

	public function testPresentEmptyElementReadsAsEmptyStringAbsentAsNull(): void
	{
		$node = $this->parse('<Invoice xmlns="http://isdoc.cz/namespace/2013"><X/></Invoice>');

		// A present-but-empty element exists and reads as an empty string; only an absent element is null.
		self::assertTrue($node->has('X'));
		self::assertSame('', $node->getString('X'));
		self::assertNull($node->getString('Y'));
	}

	public function testChildAndChildren(): void
	{
		$node = $this->parse(<<<'XML'
			<Invoice xmlns="http://isdoc.cz/namespace/2013">
				<Lines><Line><ID>1</ID></Line><Line><ID>2</ID></Line></Lines>
			</Invoice>
			XML);

		$lines = $node->getChild('Lines');
		self::assertNotNull($lines);

		$items = $lines->getChildren('Line');
		self::assertCount(2, $items);
		self::assertSame('1', $items[0]->getString('ID'));
		self::assertSame('2', $items[1]->getString('ID'));
		self::assertNull($node->getChild('Missing'));
		self::assertSame([], $node->getChildren('Missing'));
	}

	public function testMalformedIntThrowsPathTagged(): void
	{
		$node = $this->parse(
			'<Invoice xmlns="http://isdoc.cz/namespace/2013"><Sub><N>x</N></Sub></Invoice>',
		);

		$sub = $node->getChild('Sub');
		self::assertNotNull($sub);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage("Value 'Invoice/Sub/N' ('x') is not a valid integer.");
		$sub->getInt('N');
	}

	public function testMalformedBoolThrows(): void
	{
		$node = $this->parse('<Invoice xmlns="http://isdoc.cz/namespace/2013"><B>maybe</B></Invoice>');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage("Value 'Invoice/B' ('maybe') is not a valid boolean.");
		$node->getBool('B');
	}

	public function testMalformedDateThrows(): void
	{
		$node = $this->parse('<Invoice xmlns="http://isdoc.cz/namespace/2013"><D>16.8.2021</D></Invoice>');

		$this->expectException(Exception::class);
		$node->getDate('D');
	}

	/**
	 * @throws Exception
	 */
	public function testReadEnum(): void
	{
		$node = $this->parse(<<<'XML'
			<Invoice xmlns="http://isdoc.cz/namespace/2013"><M>1</M><B>S</B><Empty></Empty></Invoice>
			XML);

		// Both an int- and a string-backed enum resolve to the matching case.
		self::assertSame(VATCalculationMethod::FromTheTop, $node->getEnum('M', VATCalculationMethod::class));
		self::assertSame(BatchOrSerialNumber::SerialNumber, $node->getEnum('B', BatchOrSerialNumber::class));

		// Absent or present-but-empty reads as null for either backing type.
		self::assertNull($node->getEnum('Missing', VATCalculationMethod::class));
		self::assertNull($node->getEnum('Empty', VATCalculationMethod::class));
		self::assertNull($node->getEnum('Empty', BatchOrSerialNumber::class));
	}

	public function testWriteEnumStoresBackingValue(): void
	{
		$node = Node::create('ClassifiedTaxCategory', ['VATCalculationMethod', 'BatchOrSerialNumber']);
		$node->setEnum('VATCalculationMethod', VATCalculationMethod::FromTheTop);
		$node->setEnum('BatchOrSerialNumber', BatchOrSerialNumber::Batch);

		$xml = $this->serialize($node);
		self::assertStringContainsString('<VATCalculationMethod>1</VATCalculationMethod>', $xml);
		self::assertStringContainsString('<BatchOrSerialNumber>B</BatchOrSerialNumber>', $xml);

		// Null removes the element.
		$node->setEnum('VATCalculationMethod', null);
		self::assertStringNotContainsString('VATCalculationMethod', $this->serialize($node));
	}

	public function testMalformedEnumThrowsPathTagged(): void
	{
		$node = $this->parse('<Invoice xmlns="http://isdoc.cz/namespace/2013"><M>9</M></Invoice>');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage("Value 'Invoice/M' ('9') is not a valid VATCalculationMethod.");
		$node->getEnum('M', VATCalculationMethod::class);
	}

	public function testWriteScalarsInSchemaOrder(): void
	{
		$node = Node::create('Invoice', ['DocumentType', 'ID', 'UUID', 'IssueDate']);

		// set out of order on purpose
		$node->setString('IssueDate', '2021-08-16');
		$node->setString('ID', '12345');
		$node->setInt('DocumentType', 1);

		$xml = $this->serialize($node);
		self::assertStringContainsString('<DocumentType>1</DocumentType>', $xml);
		self::assertLessThan(strpos($xml, '<ID>'), strpos($xml, '<DocumentType>'));
		self::assertLessThan(strpos($xml, '<IssueDate>'), strpos($xml, '<ID>'));
	}

	public function testWriteTypedSettersAndAttributes(): void
	{
		$node = Node::create('Invoice', ['VATApplicable']);
		$node->setBool('VATApplicable', false);
		$node->setString('@version', '6.0.2');

		$xml = $this->serialize($node);
		self::assertStringContainsString('version="6.0.2"', $xml);
		self::assertStringContainsString('<VATApplicable>false</VATApplicable>', $xml);
	}

	public function testWriteNullRemoves(): void
	{
		$node = Node::create('Invoice', ['ID']);
		$node->setString('ID', '1');
		$node->setString('@a', 'x');
		self::assertTrue($node->has('ID'));

		$node->setString('ID', null);
		$node->setString('@a', null);
		self::assertFalse($node->has('ID'));
		self::assertFalse($node->has('@a'));
	}

	public function testEmptyTextStaysSelfClosing(): void
	{
		$node = Node::create('ElectronicPossibilityAgreementReference');
		$node->text = null;

		// truly empty (no text node) must serialize self-closing, matching the legacy snapshots
		$xml = $this->serialize($node);
		self::assertStringEndsWith('/>', trim($xml));
		self::assertStringNotContainsString('></ElectronicPossibilityAgreementReference>', $xml);
	}

	public function testSetChildAdoptsAcrossDocumentsAndOrders(): void
	{
		$parent = Node::create('PostalAddress', ['StreetName', 'BuildingNumber', 'Country']);
		$parent->setString('BuildingNumber', '1234');
		$parent->setString('StreetName', 'Dlouhá');

		$country = Node::create('Country', ['IdentificationCode', 'Name']);
		$country->setString('IdentificationCode', 'CZ');
		$country->setString('Name', 'Česká republika');

		$parent->setChild('Country', $country);

		// mutating the child view after attachment must still reach the tree (adoptNode keeps it live)
		$country->setString('Name', 'Czechia');

		$xml = $this->serialize($parent);
		self::assertStringContainsString('<IdentificationCode>CZ</IdentificationCode>', $xml);
		self::assertStringContainsString('<Name>Czechia</Name>', $xml);
		self::assertLessThan(strpos($xml, '<Country>'), strpos($xml, '<BuildingNumber>'));
		self::assertLessThan(strpos($xml, '<BuildingNumber>'), strpos($xml, '<StreetName>'));
	}

	public function testAddChildAppendsRepeated(): void
	{
		$lines = Node::create('InvoiceLines', ['InvoiceLine']);

		foreach (['1', '2', '3'] as $id) {
			$line = Node::create('InvoiceLine', ['ID']);
			$line->setString('ID', $id);
			$lines->addChild('InvoiceLine', $line);
		}

		self::assertCount(3, $lines->getChildren('InvoiceLine'));
		$ids = array_map(static fn(Node $n): ?string => $n->getString('ID'), $lines->getChildren('InvoiceLine'));
		self::assertSame(['1', '2', '3'], $ids);
	}

	public function testSetChildReplaces(): void
	{
		$parent = Node::create('Party', ['Contact']);

		$first = Node::create('Contact', ['Name']);
		$first->setString('Name', 'first');
		$parent->setChild('Contact', $first);

		$second = Node::create('Contact', ['Name']);
		$second->setString('Name', 'second');
		$parent->setChild('Contact', $second);

		self::assertCount(1, $parent->getChildren('Contact'));
		self::assertSame('second', $parent->getChildren('Contact')[0]->getString('Name'));

		$parent->setChild('Contact', null);
		self::assertCount(0, $parent->getChildren('Contact'));
	}

	/**
	 * @throws Exception
	 */
	public function testRoundTrip(): void
	{
		$node = Node::create('Invoice', ['DocumentType', 'ID', 'VATApplicable', 'IssueDate']);
		$node->setInt('DocumentType', 1);
		$node->setString('ID', '12345');
		$node->setBool('VATApplicable', true);
		$node->setDate('IssueDate', new DateTimeImmutable('2021-08-16'));

		$reparsed = $this->parse($this->serialize($node));
		self::assertSame(1, $reparsed->getInt('DocumentType'));
		self::assertSame('12345', $reparsed->getString('ID'));
		self::assertTrue($reparsed->getBool('VATApplicable'));
		self::assertSame('2021-08-16', $reparsed->getDate('IssueDate')?->format('Y-m-d'));
	}

}