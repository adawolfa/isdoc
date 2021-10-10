<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Header collection of referenced purchase order(s).
 *
 * @Map("OrderReference")
 * @extends Collection<Order>
 */
class OrderReferences extends Collection
{

	/** @return ArrayIterator|Order[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(Order $order): self
	{
		$this->items[] = $order;
		return $this;
	}

}