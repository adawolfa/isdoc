<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Invoice lines collection.
 * @implements IteratorAggregate<int, InvoiceLine>
 */
class InvoiceLines implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, InvoiceLine> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('InvoiceLine', InvoiceLine::class);
	}

	public function add(InvoiceLine $invoiceLine): self
	{
		$this->node->addChild('InvoiceLine', $invoiceLine);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('InvoiceLine'));
	}

}