<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use ArrayIterator;

/**
 * Information about a total amount of a particular type of tax.
 *
 * @Map("TaxSubTotal")
 * @extends Collection<TaxSubTotal>
 * @property string|null $taxAmountCurr
 * @property string      $taxAmount
 */
class TaxTotal extends Collection
{

	/**
	 * Amount.
	 *
	 * @Map("TaxAmountCurr")
	 */
	private ?string $taxAmountCurr = null;

	/**
	 * Amount.
	 *
	 * @Map("TaxAmount")
	 */
	private string $taxAmount;

	public function __construct(string $taxAmount)
	{
		$this->setTaxAmount($taxAmount);
	}

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

	public function getTaxAmountCurr(): ?string
	{
		return $this->taxAmountCurr;
	}

	public function setTaxAmountCurr(?string $taxAmountCurr): self
	{
		Restriction::decimal($taxAmountCurr);
		$this->taxAmountCurr = $taxAmountCurr;
		return $this;
	}

	public function getTaxAmount(): string
	{
		return $this->taxAmount;
	}

	public function setTaxAmount(string $taxAmount): self
	{
		Restriction::decimal($taxAmount);
		$this->taxAmount = $taxAmount;
		return $this;
	}

}