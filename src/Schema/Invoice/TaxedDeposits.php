<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Collection of taxed deposits (advance invoices with VAT).
 *
 * @extends Collection<TaxedDeposit>
 */
#[Map('TaxedDeposit', TaxedDeposit::class)]
class TaxedDeposits extends Collection
{

	/** @return ArrayIterator|TaxedDeposit[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(TaxedDeposit $taxedDeposit): self
	{
		$this->items[] = $taxedDeposit;
		return $this;
	}

}