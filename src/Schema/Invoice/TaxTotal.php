<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Deprecations;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use ArrayIterator;
use BcMath;

/**
 * Information about a total amount of a particular type of tax.
 *
 * @extends Collection<TaxSubTotal>
 * @property string|BcMath\Number|null $taxAmountCurr
 * @property string|BcMath\Number      $taxAmount
 */
#[Map('TaxSubTotal', TaxSubTotal::class)]
class TaxTotal extends Collection
{

	/** Amount. */
	#[Map('TaxAmountCurr')]
	private ?string $taxAmountCurr = null;

	/** Amount. */
	#[Map('TaxAmount')]
	private string $taxAmount;

	public function __construct(string|BcMath\Number $taxAmount)
	{
		$this->setTaxAmount($taxAmount);
	}

	/** @return ArrayIterator<int, TaxSubTotal> */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(TaxSubTotal $taxSubTotal): self
	{
		$this->items[] = $taxSubTotal;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxAmountCurr} property instead. */
	public function getTaxAmountCurr(): ?string
	{
		return $this->taxAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxAmountCurr} property instead. */
	public function setTaxAmountCurr(string|BcMath\Number|null $taxAmountCurr): self
	{
		Deprecations::number($taxAmountCurr);
		$taxAmountCurr = $taxAmountCurr === null ? null : (string) $taxAmountCurr;
		Restriction::decimal($taxAmountCurr);
		$this->taxAmountCurr = $taxAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxAmount} property instead. */
	public function getTaxAmount(): string
	{
		return $this->taxAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxAmount} property instead. */
	public function setTaxAmount(string|BcMath\Number $taxAmount): self
	{
		Deprecations::number($taxAmount);
		$taxAmount = (string) $taxAmount;
		Restriction::decimal($taxAmount);
		$this->taxAmount = $taxAmount;
		return $this;
	}

}