<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Collection of taxed deposits (advance invoices with VAT).
 * @implements IteratorAggregate<int, TaxedDeposit>
 */
class TaxedDeposits implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, TaxedDeposit> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('TaxedDeposit', TaxedDeposit::class);
	}

	public function add(TaxedDeposit $taxedDeposit): self
	{
		$this->node->addChild('TaxedDeposit', $taxedDeposit);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('TaxedDeposit'));
	}

}