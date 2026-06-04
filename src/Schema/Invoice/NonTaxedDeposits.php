<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Collection of proforma invoices (without VAT).
 * @implements IteratorAggregate<int, NonTaxedDeposit>
 */
class NonTaxedDeposits implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, NonTaxedDeposit> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('NonTaxedDeposit', NonTaxedDeposit::class);
	}

	public function add(NonTaxedDeposit $nonTaxedDeposit): self
	{
		$this->node->addChild('NonTaxedDeposit', $nonTaxedDeposit);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('NonTaxedDeposit'));
	}

}