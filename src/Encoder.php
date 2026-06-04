<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC\XML\Document;
use Adawolfa\ISDOC\XML\Exception as XmlException;
use Adawolfa\ISDOC\XML\Node;

/**
 * Serializes a {@see Schema\Invoice} view's backing document to an ISDOC XML string.
 *
 * Two final passes run first: any {@see Finalizable} view flushes its computed values into the DOM, and the
 * deferred reference {@code @id}/{@code @ref} pairs are minted ({@see Node::finalizeReferences()}). The tree is
 * then serialized verbatim.
 *
 * @internal
 */
final class Encoder
{

	/** @throws EncoderException */
	public function encode(Schema\Invoice $invoice): string
	{
		try {

			// A Finalizable view (the decorated invoice) reads its typed totals here, which can surface a lazy
			// XML\Exception; wrapping the whole pass keeps encode() throwing only EncoderException.
			if ($invoice instanceof Finalizable) {
				$invoice->finalizeForWrite();
			}

			Node::finalizeReferences($invoice->node->dom);

			return Document::serialize($invoice->node);

		} catch (XmlException $exception) {
			throw new EncoderException('The document could not be encoded.', 0, $exception);
		}
	}

}