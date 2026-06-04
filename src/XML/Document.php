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

	private const int ParseOptions = LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT;

	/** @throws Exception */
	public static function parse(string $xml): Node
	{
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

}