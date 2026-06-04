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

	/**
	 * SEC-02 regression guard: a DTD is rejected outright, so an external entity never reaches the parser and the
	 * secret file cannot be read (no XXE). Allowing the DOCTYPE through — or adding LIBXML_NOENT / LIBXML_DTDLOAD to
	 * {@see Document}'s ParseOptions — is exactly the silent reintroduction the comment on ParseOptions warns against.
	 */
	public function testXxeEntityIsNotSubstituted(): void
	{
		$secretFile = tempnam(sys_get_temp_dir(), 'isdoc_xxe_secret');
		self::assertNotFalse($secretFile);
		file_put_contents($secretFile, 'TOP-SECRET-7f3a2b');

		try {

			$uri = 'file:///' . str_replace('\\', '/', $secretFile);
			$xml = '<?xml version="1.0"?>'
				. '<!DOCTYPE Invoice [<!ENTITY xxe SYSTEM "' . $uri . '">]>'
				. '<Invoice xmlns="http://isdoc.cz/namespace/2013"><ID>&xxe;</ID></Invoice>';

			$this->expectException(Exception::class);
			Document::parse($xml);

		} finally {
			@unlink($secretFile);
		}
	}

	/**
	 * SEC-02 regression guard: a billion-laughs payload rides in on a DTD, which is rejected outright, so the nested
	 * internal entities are never given the chance to amplify. (The old "entities stay inert" assumption was
	 * libxml-version-dependent under the new Dom API — some builds expanded them, others rejected the document —
	 * which is why the DTD itself is now refused.)
	 */
	public function testBillionLaughsDoesNotExpand(): void
	{
		$xml = '<?xml version="1.0"?>'
			. '<!DOCTYPE Invoice ['
			. '<!ENTITY a "AAAAAAAAAA">'
			. '<!ENTITY b "&a;&a;&a;&a;&a;&a;&a;&a;&a;&a;">'
			. '<!ENTITY c "&b;&b;&b;&b;&b;&b;&b;&b;&b;&b;">'
			. ']><Invoice xmlns="http://isdoc.cz/namespace/2013"><ID>&c;</ID></Invoice>';

		$this->expectException(Exception::class);
		Document::parse($xml);
	}

	/**
	 * The DTD guard scans only the prolog: a `<!DOCTYPE` that is element content (here inside CDATA) or text inside
	 * a prolog comment is data, not a declaration, and must not trip the guard — otherwise a free-text field could
	 * make a perfectly valid invoice unreadable.
	 *
	 * @throws Exception
	 */
	public function testDoctypeGuardScansPrologOnly(): void
	{
		$cdata = '<Invoice xmlns="http://isdoc.cz/namespace/2013">'
			. '<ID><![CDATA[<!DOCTYPE evil>]]></ID></Invoice>';
		self::assertFalse(Document::declaresDoctype($cdata));
		self::assertSame('<!DOCTYPE evil>', Document::parse($cdata)->getString('ID'));

		$prologComment = '<?xml version="1.0"?><!-- <!DOCTYPE evil> -->'
			. '<Invoice xmlns="http://isdoc.cz/namespace/2013"><ID>ok</ID></Invoice>';
		self::assertFalse(Document::declaresDoctype($prologComment));
		self::assertSame('ok', Document::parse($prologComment)->getString('ID'));
	}

}