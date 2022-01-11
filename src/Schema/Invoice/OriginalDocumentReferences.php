<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Header collection of referenced original documents.
 *
 * @extends Collection<OriginalDocument>
 */
#[Map('OriginalDocumentReference', OriginalDocument::class)]
class OriginalDocumentReferences extends Collection
{

	/** @return ArrayIterator|OriginalDocument[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(OriginalDocument $originalDocument): self
	{
		$this->items[] = $originalDocument;
		return $this;
	}

}