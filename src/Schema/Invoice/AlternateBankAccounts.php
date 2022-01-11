<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Collection of alternative bank accounts.
 *
 * @extends Collection<AlternateBankAccount>
 */
#[Map('AlternateBankAccount', AlternateBankAccount::class)]
class AlternateBankAccounts extends Collection
{

	/** @return ArrayIterator|AlternateBankAccount[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(AlternateBankAccount $alternateBankAccount): self
	{
		$this->items[] = $alternateBankAccount;
		return $this;
	}

}