<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Batch or serial number collection.
 * @implements IteratorAggregate<int, StoreBatch>
 */
class StoreBatches implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, StoreBatch> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('StoreBatch', StoreBatch::class);
	}

	public function add(StoreBatch $storeBatch): self
	{
		$this->node->addChild('StoreBatch', $storeBatch);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('StoreBatch'));
	}

}