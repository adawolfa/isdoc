<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Information about payment means.
 *
 * @extends Collection<Payment>
 * @property AlternateBankAccounts|null $alternateBankAccounts
 */
#[Map('Payment', Payment::class)]
class PaymentMeans extends Collection
{

	/** Collection of alternative bank accounts. */
	#[Map('AlternateBankAccounts')]
	private ?AlternateBankAccounts $alternateBankAccounts = null;

	/** @return ArrayIterator<int, Payment> */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(Payment $payment): self
	{
		$this->items[] = $payment;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alternateBankAccounts} property instead. */
	public function getAlternateBankAccounts(): ?AlternateBankAccounts
	{
		return $this->alternateBankAccounts;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alternateBankAccounts} property instead. */
	public function setAlternateBankAccounts(?AlternateBankAccounts $alternateBankAccounts): self
	{
		$this->alternateBankAccounts = $alternateBankAccounts;
		return $this;
	}

}