<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Information about a total amount of a particular type of tax.
 * @implements IteratorAggregate<int, TaxSubTotal>
 */
class TaxTotal implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** Amount. */
	public ?Number $taxAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('TaxAmountCurr');
		set {
			$this->node->setNumber('TaxAmountCurr', $value);
		}
	}

	/** Amount. */
	public Number $taxAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('TaxAmount');
		set {
			$this->node->setNumber('TaxAmount', $value);
		}
	}

	public function __construct(
		Number $taxAmount,
	)
	{
		$this->taxAmount = $taxAmount;
	}

	/** @return Generator<int, TaxSubTotal> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('TaxSubTotal', TaxSubTotal::class);
	}

	public function add(TaxSubTotal $taxSubTotal): self
	{
		$this->node->addChild('TaxSubTotal', $taxSubTotal);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('TaxSubTotal'));
	}

}