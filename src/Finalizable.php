<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

/**
 * A view whose backing element needs a last pass before serialization — e.g. the decorated invoice, which
 * flushes its auto-computed monetary totals into the DOM so they appear in the output. Implementors are invoked
 * by the {@see Encoder} just before the document is written, after which the tree is serialized verbatim.
 *
 * @internal
 */
interface Finalizable
{

	/** @throws XML\Exception */
	public function finalizeForWrite(): void;

}