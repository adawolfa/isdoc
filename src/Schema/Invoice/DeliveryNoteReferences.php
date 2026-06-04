<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Header collection of referenced delivery notes.
 * @implements IteratorAggregate<int, DeliveryNote>
 */
class DeliveryNoteReferences implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, DeliveryNote> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('DeliveryNoteReference', DeliveryNote::class);
	}

	public function add(DeliveryNote $deliveryNoteReference): self
	{
		$this->node->addChild('DeliveryNoteReference', $deliveryNoteReference);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('DeliveryNoteReference'));
	}

}