<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Batch or serial number collection.
 *
 * @Map("StoreBatch")
 * @extends Collection<StoreBatch>
 */
class StoreBatches extends Collection
{

	/** @return ArrayIterator|StoreBatch[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(StoreBatch $storeBatch): self
	{
		$this->items[] = $storeBatch;
		return $this;
	}

}