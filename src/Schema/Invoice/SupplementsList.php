<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Collection of document attachments. Exactly one attachment can be document preview marked by preview="true".
 * @implements IteratorAggregate<int, Supplement>
 */
class SupplementsList implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, Supplement> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('Supplement', Supplement::class);
	}

	public function add(Supplement $supplement): self
	{
		$this->node->addChild('Supplement', $supplement);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('Supplement'));
	}

}