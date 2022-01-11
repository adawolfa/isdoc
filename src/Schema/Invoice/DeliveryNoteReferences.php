<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Header collection of referenced delivery notes.
 *
 * @extends Collection<DeliveryNote>
 */
#[Map('DeliveryNoteReference', DeliveryNote::class)]
class DeliveryNoteReferences extends Collection
{

	/** @return ArrayIterator|DeliveryNote[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(DeliveryNote $deliveryNote): self
	{
		$this->items[] = $deliveryNote;
		return $this;
	}

}