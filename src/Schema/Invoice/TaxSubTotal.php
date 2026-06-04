<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Deprecations;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use BcMath;
use Nette\SmartObject;

/**
 * Subtotals for one tax rate.
 *
 * @property string|BcMath\Number|null $taxableAmountCurr
 * @property string|BcMath\Number      $taxableAmount
 * @property string|BcMath\Number|null $taxAmountCurr
 * @property string|BcMath\Number      $taxAmount
 * @property string|BcMath\Number|null $taxInclusiveAmountCurr
 * @property string|BcMath\Number      $taxInclusiveAmount
 * @property string|BcMath\Number|null $alreadyClaimedTaxableAmountCurr
 * @property string|BcMath\Number      $alreadyClaimedTaxableAmount
 * @property string|BcMath\Number|null $alreadyClaimedTaxAmountCurr
 * @property string|BcMath\Number      $alreadyClaimedTaxAmount
 * @property string|BcMath\Number|null $alreadyClaimedTaxInclusiveAmountCurr
 * @property string|BcMath\Number      $alreadyClaimedTaxInclusiveAmount
 * @property string|BcMath\Number|null $differenceTaxableAmountCurr
 * @property string|BcMath\Number      $differenceTaxableAmount
 * @property string|BcMath\Number|null $differenceTaxAmountCurr
 * @property string|BcMath\Number      $differenceTaxAmount
 * @property string|BcMath\Number|null $differenceTaxInclusiveAmountCurr
 * @property string|BcMath\Number      $differenceTaxInclusiveAmount
 * @property TaxCategory               $taxCategory
 */
class TaxSubTotal implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Tax base for rate in a foreign currency. */
	#[Map('TaxableAmountCurr')]
	private ?string $taxableAmountCurr = null;

	/** Tax base for rate in a local currency. */
	#[Map('TaxableAmount')]
	private string $taxableAmount;

	/** Tax for rate in a foreign currency. */
	#[Map('TaxAmountCurr')]
	private ?string $taxAmountCurr = null;

	/** Tax for rate in a local currency. */
	#[Map('TaxAmount')]
	private string $taxAmount;

	/** Amount including tax for rate in a foreign currency. */
	#[Map('TaxInclusiveAmountCurr')]
	private ?string $taxInclusiveAmountCurr = null;

	/** Amount including tax for rate in a local currency. */
	#[Map('TaxInclusiveAmount')]
	private string $taxInclusiveAmount;

	/** Already claimed amount for rate in a foreign currency. */
	#[Map('AlreadyClaimedTaxableAmountCurr')]
	private ?string $alreadyClaimedTaxableAmountCurr = null;

	/** Already claimed amount for rate in a local currency. */
	#[Map('AlreadyClaimedTaxableAmount')]
	private string $alreadyClaimedTaxableAmount;

	/** Already claimed tax for rate in a foreign currency. */
	#[Map('AlreadyClaimedTaxAmountCurr')]
	private ?string $alreadyClaimedTaxAmountCurr = null;

	/** Already claimed tax for rate in a local currency. */
	#[Map('AlreadyClaimedTaxAmount')]
	private string $alreadyClaimedTaxAmount;

	/** Already claimed amount including tax for rate in a foreign currency. */
	#[Map('AlreadyClaimedTaxInclusiveAmountCurr')]
	private ?string $alreadyClaimedTaxInclusiveAmountCurr = null;

	/** Already claimed amount including tax for rate in a local currency. */
	#[Map('AlreadyClaimedTaxInclusiveAmount')]
	private string $alreadyClaimedTaxInclusiveAmount;

	/** Difference in the amount in a foreign currency. */
	#[Map('DifferenceTaxableAmountCurr')]
	private ?string $differenceTaxableAmountCurr = null;

	/** Difference in the amount in a local currency. */
	#[Map('DifferenceTaxableAmount')]
	private string $differenceTaxableAmount;

	/** Difference in the tax in a foreign currency. */
	#[Map('DifferenceTaxAmountCurr')]
	private ?string $differenceTaxAmountCurr = null;

	/** Difference in the tax in a local currency. */
	#[Map('DifferenceTaxAmount')]
	private string $differenceTaxAmount;

	/** Difference including tax in a foreign currency. */
	#[Map('DifferenceTaxInclusiveAmountCurr')]
	private ?string $differenceTaxInclusiveAmountCurr = null;

	/** Difference including tax in a local currency. */
	#[Map('DifferenceTaxInclusiveAmount')]
	private string $differenceTaxInclusiveAmount;

	/** Information about a tax rate. */
	#[Map('TaxCategory')]
	private TaxCategory $taxCategory;

	public function __construct(
		string|BcMath\Number $taxableAmount,
		string|BcMath\Number $taxAmount,
		string|BcMath\Number $taxInclusiveAmount,
		string|BcMath\Number $alreadyClaimedTaxableAmount,
		string|BcMath\Number $alreadyClaimedTaxAmount,
		string|BcMath\Number $alreadyClaimedTaxInclusiveAmount,
		string|BcMath\Number $differenceTaxableAmount,
		string|BcMath\Number $differenceTaxAmount,
		string|BcMath\Number $differenceTaxInclusiveAmount,
		TaxCategory $taxCategory,
	) {
		$this->setTaxableAmount($taxableAmount);
		$this->setTaxAmount($taxAmount);
		$this->setTaxInclusiveAmount($taxInclusiveAmount);
		$this->setAlreadyClaimedTaxableAmount($alreadyClaimedTaxableAmount);
		$this->setAlreadyClaimedTaxAmount($alreadyClaimedTaxAmount);
		$this->setAlreadyClaimedTaxInclusiveAmount($alreadyClaimedTaxInclusiveAmount);
		$this->setDifferenceTaxableAmount($differenceTaxableAmount);
		$this->setDifferenceTaxAmount($differenceTaxAmount);
		$this->setDifferenceTaxInclusiveAmount($differenceTaxInclusiveAmount);
		$this->setTaxCategory($taxCategory);
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxableAmountCurr} property instead. */
	public function getTaxableAmountCurr(): ?string
	{
		return $this->taxableAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxableAmountCurr} property instead. */
	public function setTaxableAmountCurr(string|BcMath\Number|null $taxableAmountCurr): self
	{
		Deprecations::number($taxableAmountCurr);
		$taxableAmountCurr = $taxableAmountCurr === null ? null : (string) $taxableAmountCurr;
		Restriction::decimal($taxableAmountCurr);
		$this->taxableAmountCurr = $taxableAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxableAmount} property instead. */
	public function getTaxableAmount(): string
	{
		return $this->taxableAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxableAmount} property instead. */
	public function setTaxableAmount(string|BcMath\Number $taxableAmount): self
	{
		Deprecations::number($taxableAmount);
		$taxableAmount = (string) $taxableAmount;
		Restriction::decimal($taxableAmount);
		$this->taxableAmount = $taxableAmount;
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

	/** @deprecated Method accessors are deprecated, use {@see $taxInclusiveAmountCurr} property instead. */
	public function getTaxInclusiveAmountCurr(): ?string
	{
		return $this->taxInclusiveAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxInclusiveAmountCurr} property instead. */
	public function setTaxInclusiveAmountCurr(string|BcMath\Number|null $taxInclusiveAmountCurr): self
	{
		Deprecations::number($taxInclusiveAmountCurr);
		$taxInclusiveAmountCurr = $taxInclusiveAmountCurr === null ? null : (string) $taxInclusiveAmountCurr;
		Restriction::decimal($taxInclusiveAmountCurr);
		$this->taxInclusiveAmountCurr = $taxInclusiveAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxInclusiveAmount} property instead. */
	public function getTaxInclusiveAmount(): string
	{
		return $this->taxInclusiveAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxInclusiveAmount} property instead. */
	public function setTaxInclusiveAmount(string|BcMath\Number $taxInclusiveAmount): self
	{
		Deprecations::number($taxInclusiveAmount);
		$taxInclusiveAmount = (string) $taxInclusiveAmount;
		Restriction::decimal($taxInclusiveAmount);
		$this->taxInclusiveAmount = $taxInclusiveAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxableAmountCurr} property instead. */
	public function getAlreadyClaimedTaxableAmountCurr(): ?string
	{
		return $this->alreadyClaimedTaxableAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxableAmountCurr} property instead. */
	public function setAlreadyClaimedTaxableAmountCurr(string|BcMath\Number|null $alreadyClaimedTaxableAmountCurr): self
	{
		Deprecations::number($alreadyClaimedTaxableAmountCurr);
		$alreadyClaimedTaxableAmountCurr = $alreadyClaimedTaxableAmountCurr === null ? null : (string) $alreadyClaimedTaxableAmountCurr;
		Restriction::decimal($alreadyClaimedTaxableAmountCurr);
		$this->alreadyClaimedTaxableAmountCurr = $alreadyClaimedTaxableAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxableAmount} property instead. */
	public function getAlreadyClaimedTaxableAmount(): string
	{
		return $this->alreadyClaimedTaxableAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxableAmount} property instead. */
	public function setAlreadyClaimedTaxableAmount(string|BcMath\Number $alreadyClaimedTaxableAmount): self
	{
		Deprecations::number($alreadyClaimedTaxableAmount);
		$alreadyClaimedTaxableAmount = (string) $alreadyClaimedTaxableAmount;
		Restriction::decimal($alreadyClaimedTaxableAmount);
		$this->alreadyClaimedTaxableAmount = $alreadyClaimedTaxableAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxAmountCurr} property instead. */
	public function getAlreadyClaimedTaxAmountCurr(): ?string
	{
		return $this->alreadyClaimedTaxAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxAmountCurr} property instead. */
	public function setAlreadyClaimedTaxAmountCurr(string|BcMath\Number|null $alreadyClaimedTaxAmountCurr): self
	{
		Deprecations::number($alreadyClaimedTaxAmountCurr);
		$alreadyClaimedTaxAmountCurr = $alreadyClaimedTaxAmountCurr === null ? null : (string) $alreadyClaimedTaxAmountCurr;
		Restriction::decimal($alreadyClaimedTaxAmountCurr);
		$this->alreadyClaimedTaxAmountCurr = $alreadyClaimedTaxAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxAmount} property instead. */
	public function getAlreadyClaimedTaxAmount(): string
	{
		return $this->alreadyClaimedTaxAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxAmount} property instead. */
	public function setAlreadyClaimedTaxAmount(string|BcMath\Number $alreadyClaimedTaxAmount): self
	{
		Deprecations::number($alreadyClaimedTaxAmount);
		$alreadyClaimedTaxAmount = (string) $alreadyClaimedTaxAmount;
		Restriction::decimal($alreadyClaimedTaxAmount);
		$this->alreadyClaimedTaxAmount = $alreadyClaimedTaxAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxInclusiveAmountCurr} property instead. */
	public function getAlreadyClaimedTaxInclusiveAmountCurr(): ?string
	{
		return $this->alreadyClaimedTaxInclusiveAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxInclusiveAmountCurr} property instead. */
	public function setAlreadyClaimedTaxInclusiveAmountCurr(
		string|BcMath\Number|null $alreadyClaimedTaxInclusiveAmountCurr,
	): self
	{
		Deprecations::number($alreadyClaimedTaxInclusiveAmountCurr);
		$alreadyClaimedTaxInclusiveAmountCurr = $alreadyClaimedTaxInclusiveAmountCurr === null ? null : (string) $alreadyClaimedTaxInclusiveAmountCurr;
		Restriction::decimal($alreadyClaimedTaxInclusiveAmountCurr);
		$this->alreadyClaimedTaxInclusiveAmountCurr = $alreadyClaimedTaxInclusiveAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxInclusiveAmount} property instead. */
	public function getAlreadyClaimedTaxInclusiveAmount(): string
	{
		return $this->alreadyClaimedTaxInclusiveAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxInclusiveAmount} property instead. */
	public function setAlreadyClaimedTaxInclusiveAmount(string|BcMath\Number $alreadyClaimedTaxInclusiveAmount): self
	{
		Deprecations::number($alreadyClaimedTaxInclusiveAmount);
		$alreadyClaimedTaxInclusiveAmount = (string) $alreadyClaimedTaxInclusiveAmount;
		Restriction::decimal($alreadyClaimedTaxInclusiveAmount);
		$this->alreadyClaimedTaxInclusiveAmount = $alreadyClaimedTaxInclusiveAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxableAmountCurr} property instead. */
	public function getDifferenceTaxableAmountCurr(): ?string
	{
		return $this->differenceTaxableAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxableAmountCurr} property instead. */
	public function setDifferenceTaxableAmountCurr(string|BcMath\Number|null $differenceTaxableAmountCurr): self
	{
		Deprecations::number($differenceTaxableAmountCurr);
		$differenceTaxableAmountCurr = $differenceTaxableAmountCurr === null ? null : (string) $differenceTaxableAmountCurr;
		Restriction::decimal($differenceTaxableAmountCurr);
		$this->differenceTaxableAmountCurr = $differenceTaxableAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxableAmount} property instead. */
	public function getDifferenceTaxableAmount(): string
	{
		return $this->differenceTaxableAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxableAmount} property instead. */
	public function setDifferenceTaxableAmount(string|BcMath\Number $differenceTaxableAmount): self
	{
		Deprecations::number($differenceTaxableAmount);
		$differenceTaxableAmount = (string) $differenceTaxableAmount;
		Restriction::decimal($differenceTaxableAmount);
		$this->differenceTaxableAmount = $differenceTaxableAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxAmountCurr} property instead. */
	public function getDifferenceTaxAmountCurr(): ?string
	{
		return $this->differenceTaxAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxAmountCurr} property instead. */
	public function setDifferenceTaxAmountCurr(string|BcMath\Number|null $differenceTaxAmountCurr): self
	{
		Deprecations::number($differenceTaxAmountCurr);
		$differenceTaxAmountCurr = $differenceTaxAmountCurr === null ? null : (string) $differenceTaxAmountCurr;
		Restriction::decimal($differenceTaxAmountCurr);
		$this->differenceTaxAmountCurr = $differenceTaxAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxAmount} property instead. */
	public function getDifferenceTaxAmount(): string
	{
		return $this->differenceTaxAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxAmount} property instead. */
	public function setDifferenceTaxAmount(string|BcMath\Number $differenceTaxAmount): self
	{
		Deprecations::number($differenceTaxAmount);
		$differenceTaxAmount = (string) $differenceTaxAmount;
		Restriction::decimal($differenceTaxAmount);
		$this->differenceTaxAmount = $differenceTaxAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxInclusiveAmountCurr} property instead. */
	public function getDifferenceTaxInclusiveAmountCurr(): ?string
	{
		return $this->differenceTaxInclusiveAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxInclusiveAmountCurr} property instead. */
	public function setDifferenceTaxInclusiveAmountCurr(
		string|BcMath\Number|null $differenceTaxInclusiveAmountCurr,
	): self
	{
		Deprecations::number($differenceTaxInclusiveAmountCurr);
		$differenceTaxInclusiveAmountCurr = $differenceTaxInclusiveAmountCurr === null ? null : (string) $differenceTaxInclusiveAmountCurr;
		Restriction::decimal($differenceTaxInclusiveAmountCurr);
		$this->differenceTaxInclusiveAmountCurr = $differenceTaxInclusiveAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxInclusiveAmount} property instead. */
	public function getDifferenceTaxInclusiveAmount(): string
	{
		return $this->differenceTaxInclusiveAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxInclusiveAmount} property instead. */
	public function setDifferenceTaxInclusiveAmount(string|BcMath\Number $differenceTaxInclusiveAmount): self
	{
		Deprecations::number($differenceTaxInclusiveAmount);
		$differenceTaxInclusiveAmount = (string) $differenceTaxInclusiveAmount;
		Restriction::decimal($differenceTaxInclusiveAmount);
		$this->differenceTaxInclusiveAmount = $differenceTaxInclusiveAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxCategory} property instead. */
	public function getTaxCategory(): TaxCategory
	{
		return $this->taxCategory;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxCategory} property instead. */
	public function setTaxCategory(TaxCategory $taxCategory): self
	{
		$this->taxCategory = $taxCategory;
		return $this;
	}

}