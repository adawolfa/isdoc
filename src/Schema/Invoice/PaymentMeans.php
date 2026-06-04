<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Information about payment means.
 * @implements IteratorAggregate<int, Payment>
 */
class PaymentMeans implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** Collection of alternative bank accounts. */
	public ?AlternateBankAccounts $alternateBankAccounts {
		get => $this->node->getChild('AlternateBankAccounts', AlternateBankAccounts::class);
		set { $this->node->setChild('AlternateBankAccounts', $value); }
	}

	/** @return Generator<int, Payment> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('Payment', Payment::class);
	}

	public function add(Payment $payment): self
	{
		$this->node->addChild('Payment', $payment);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('Payment'));
	}

}