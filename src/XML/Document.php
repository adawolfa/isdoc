<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\XML;

use Dom\Element;
use Dom\XMLDocument;
use DOMException;

/**
 * Parses ISDOC XML into a root {@see Node} and serialises a root node back to a string.
 *
 * The single source of XML parsing and formatting for the library — we own namespace handling and output
 * layout here rather than delegating to a serializer component.
 *
 * @internal
 */
final class Document
{

	/**
	 * Security invariant — this set is deliberately XXE-safe and MUST stay that way:
	 *
	 * - `LIBXML_NOENT` / `LIBXML_DTDLOAD` MUST NOT be added. Either one re-enables entity substitution, turning an
	 *   attacker-supplied `<!ENTITY xxe SYSTEM "file:///…">` into a local-file disclosure (XXE) and a quadratic
	 *   entity-expansion (billion-laughs) DoS. These flags are the second line of defence behind the DTD rejection
	 *   below, not the first: under the new {@see XMLDocument} API the inert-entity behaviour the old DOMDocument
	 *   gave is libxml-version-dependent (some builds expand internal entities, others reject the document), so the
	 *   primary guard is {@see declaresDoctype()} — a valid ISDOC never carries a DTD.
	 * - `LIBXML_NONET` MUST stay to block network fetches of external DTDs/entities (SSRF).
	 *
	 * The {@code X\Reader} manifest parse carries the same invariant (it reuses {@see declaresDoctype()}). Covered
	 * by tests/XML/DocumentTest.php (XXE, billion-laughs).
	 */
	private const int ParseOptions = LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT;

	/** @throws Exception */
	public static function parse(string $xml): Node
	{
		if (self::declaresDoctype($xml)) {
			throw new Exception('The document declares a DTD, which is not permitted.');
		}

		try {
			$document = XMLDocument::createFromString($xml, self::ParseOptions);
		} /** @noinspection PhpRedundantCatchClauseInspection */ catch (DOMException $exception) {
			throw new Exception('The document is not well-formed XML.', 0, $exception);
		}

		$root = $document->documentElement;

		if (!$root instanceof Element) {
			throw new Exception('The document has no root element.');
		}

		return Node::wrap($root);
	}

	/** @throws Exception */
	public static function serialize(Node $root): string
	{
		$element  = $root->dom;
		$document = $element->ownerDocument;

		if (!$document instanceof XMLDocument) {
			throw new Exception('The node is not owned by an XML document.');
		}

		if ($document->documentElement === null) {
			$document->appendChild($element);
		}

		$document->formatOutput = true;
		$xml = $document->saveXml();

		if ($xml === false) {
			throw new Exception('The document could not be serialised.');
		}

		return $xml;
	}

	/**
	 * Reports whether the XML prolog declares a DTD, without invoking the parser.
	 *
	 * A DOCTYPE can only legally appear in the prolog — before the root element — so only that region is scanned:
	 * the XML declaration, comments and processing instructions are skipped over, and a `<!DOCTYPE` found before
	 * the root element is reported. `<!DOCTYPE` text inside element content or a CDATA section is data, not a real
	 * DTD, and is deliberately ignored so free-text fields never trip the guard. A genuinely malformed prolog is
	 * left for {@see XMLDocument::createFromString()} to reject.
	 */
	public static function declaresDoctype(string $xml): bool
	{
		$length = strlen($xml);
		$offset = str_starts_with($xml, "\u{FEFF}") ? 3 : 0;

		while ($offset < $length) {

			$offset += strspn($xml, " \t\r\n", $offset);

			if ($offset >= $length || $xml[$offset] !== '<') {
				return false;
			}

			if (substr_compare($xml, '<?', $offset, 2) === 0) {
				$end = strpos($xml, '?>', $offset + 2);
				if ($end === false) {
					return false;
				}
				$offset = $end + 2;
				continue;
			}

			if (substr_compare($xml, '<!--', $offset, 4) === 0) {
				$end = strpos($xml, '-->', $offset + 4);
				if ($end === false) {
					return false;
				}
				$offset = $end + 3;
				continue;
			}

			// Only a DOCTYPE or the root element can begin here; the root element name never starts with '!'.
			return substr_compare($xml, '<!DOCTYPE', $offset, 9) === 0;
		}

		return false;
	}

}