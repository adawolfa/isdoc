<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Header collection of referenced purchase order(s).
 * @implements IteratorAggregate<int, Order>
 */
class OrderReferences implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, Order> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('OrderReference', Order::class);
	}

	public function add(Order $orderReference): self
	{
		$this->node->addChild('OrderReference', $orderReference);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('OrderReference'));
	}

}