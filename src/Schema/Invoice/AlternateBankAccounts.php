<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Collection of alternative bank accounts.
 * @implements IteratorAggregate<int, AlternateBankAccount>
 */
class AlternateBankAccounts implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, AlternateBankAccount> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('AlternateBankAccount', AlternateBankAccount::class);
	}

	public function add(AlternateBankAccount $alternateBankAccount): self
	{
		$this->node->addChild('AlternateBankAccount', $alternateBankAccount);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('AlternateBankAccount'));
	}

}