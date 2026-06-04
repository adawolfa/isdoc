<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC\XML;

use Adawolfa\ISDOC\XML\Document;
use Adawolfa\ISDOC\XML\Exception;
use Adawolfa\ISDOC\XML\Node;
use PHPUnit\Framework\TestCase;

final class DocumentTest extends TestCase
{

	/**
	 * @throws Exception
	 */
	public function testParseReadsRoot(): void
	{
		$node = Document::parse(
			'<Invoice xmlns="http://isdoc.cz/namespace/2013"><ID>12345</ID></Invoice>',
		);

		self::assertSame('12345', $node->getString('ID'));
		self::assertSame('Invoice', $node->dom->localName);
	}

	/**
	 * @throws Exception
	 */
	public function testSerializeCreatedTree(): void
	{
		$node = Node::create('Invoice', ['ID', 'UUID']);
		$node->setString('ID', '12345');
		$node->setString('UUID', '00000000-0000-0000-0000-000000001234');

		$xml = Document::serialize($node);

		self::assertStringStartsWith('<?xml version="1.0"', $xml);
		self::assertStringContainsString('xmlns="http://isdoc.cz/namespace/2013"', $xml);
		self::assertStringContainsString('<ID>12345</ID>', $xml);
		self::assertSame(1, substr_count($xml, 'xmlns="http://isdoc.cz/namespace/2013"'));
	}

	/**
	 * @throws Exception
	 */
	public function testRoundTrip(): void
	{
		$node = Node::create('Invoice', ['ID']);
		$node->setString('ID', 'abc');

		$reparsed = Document::parse(Document::serialize($node));
		self::assertSame('abc', $reparsed->getString('ID'));
	}

	public function testMalformedXmlThrows(): void
	{
		$this->expectException(Exception::class);
		Document::parse('<Invoice><unclosed></Invoice>');
	}

}