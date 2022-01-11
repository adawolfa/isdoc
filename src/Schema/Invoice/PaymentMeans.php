<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Information about payment means.
 *
 * @extends Collection<Payment>
 */
#[Map('Payment', Payment::class)]
class PaymentMeans extends Collection
{

	/** @return ArrayIterator|Payment[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(Payment $payment): self
	{
		$this->items[] = $payment;
		return $this;
	}

}