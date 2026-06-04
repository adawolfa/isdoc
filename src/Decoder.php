<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC\XML\Document;
use Adawolfa\ISDOC\XML\Exception as XmlException;

/**
 * Parses ISDOC XML into a lazy {@see Schema\Invoice} view.
 *
 * Parsing only fails when the document is not well-formed XML; missing or malformed values surface lazily, at
 * the moment the offending property is read, rather than aborting the whole document.
 *
 * @internal
 */
final class Decoder
{

	/**
	 * @template T of Schema\Invoice
	 * @param class-string<T> $class
	 * @return T&Schema\Invoice
	 * @throws DecoderException
	 */
	public function decode(string $xml, string $class = Schema\Invoice::class): Schema\Invoice
	{
		try {
			$node = Document::parse($xml);
		} catch (XmlException $exception) {
			throw new DecoderException('The document could not be parsed.', 0, $exception);
		}

		return $node->bind($class);
	}

}