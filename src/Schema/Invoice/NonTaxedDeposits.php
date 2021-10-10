<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Collection of proforma invoices (without VAT).
 *
 * @Map("NonTaxedDeposit")
 * @extends Collection<NonTaxedDeposit>
 */
class NonTaxedDeposits extends Collection
{

	/** @return ArrayIterator|NonTaxedDeposit[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(NonTaxedDeposit $nonTaxedDeposit): self
	{
		$this->items[] = $nonTaxedDeposit;
		return $this;
	}

}