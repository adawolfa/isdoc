<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Invoice lines collection.
 *
 * @Map("InvoiceLine")
 * @extends Collection<InvoiceLine>
 */
class InvoiceLines extends Collection
{

	/** @return ArrayIterator|InvoiceLine[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(InvoiceLine $invoiceLine): self
	{
		$this->items[] = $invoiceLine;
		return $this;
	}

}