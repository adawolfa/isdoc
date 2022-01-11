<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Information about a total amount of a particular type of tax.
 *
 * @extends Collection<TaxSubTotal>
 */
#[Map('TaxSubTotal', TaxSubTotal::class)]
class TaxTotal extends Collection
{

	/** @return ArrayIterator|TaxSubTotal[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(TaxSubTotal $taxSubTotal): self
	{
		$this->items[] = $taxSubTotal;
		return $this;
	}

}