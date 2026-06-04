<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Header collection of referenced original documents.
 * @implements IteratorAggregate<int, OriginalDocument>
 */
class OriginalDocumentReferences implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, OriginalDocument> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('OriginalDocumentReference', OriginalDocument::class);
	}

	public function add(OriginalDocument $originalDocumentReference): self
	{
		$this->node->addChild('OriginalDocumentReference', $originalDocumentReference);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('OriginalDocumentReference'));
	}

}