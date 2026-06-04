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
 * Collection of total amounts on document ending with payable amount.
 *
 * @property string|BcMath\Number      $taxExclusiveAmount
 * @property string|BcMath\Number|null $taxExclusiveAmountCurr
 * @property string|BcMath\Number      $taxInclusiveAmount
 * @property string|BcMath\Number|null $taxInclusiveAmountCurr
 * @property string|BcMath\Number      $alreadyClaimedTaxExclusiveAmount
 * @property string|BcMath\Number|null $alreadyClaimedTaxExclusiveAmountCurr
 * @property string|BcMath\Number      $alreadyClaimedTaxInclusiveAmount
 * @property string|BcMath\Number|null $alreadyClaimedTaxInclusiveAmountCurr
 * @property string|BcMath\Number      $differenceTaxExclusiveAmount
 * @property string|BcMath\Number|null $differenceTaxExclusiveAmountCurr
 * @property string|BcMath\Number      $differenceTaxInclusiveAmount
 * @property string|BcMath\Number|null $differenceTaxInclusiveAmountCurr
 * @property string|BcMath\Number|null $payableRoundingAmount
 * @property string|BcMath\Number|null $payableRoundingAmountCurr
 * @property string|BcMath\Number      $paidDepositsAmount
 * @property string|BcMath\Number|null $paidDepositsAmountCurr
 * @property string|BcMath\Number      $payableAmount
 * @property string|BcMath\Number|null $payableAmountCurr
 */
class LegalMonetaryTotal implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Total amount without tax in a local currency. */
	#[Map('TaxExclusiveAmount')]
	private string $taxExclusiveAmount;

	/** Total amount without tax in a foreign currency. */
	#[Map('TaxExclusiveAmountCurr')]
	private ?string $taxExclusiveAmountCurr = null;

	/** Total amount including tax in a local currency. */
	#[Map('TaxInclusiveAmount')]
	private string $taxInclusiveAmount;

	/** Total amount including tax in a foreign currency. */
	#[Map('TaxInclusiveAmountCurr')]
	private ?string $taxInclusiveAmountCurr = null;

	/** Total amount of all already claimed advance invoices without tax in a local currency. */
	#[Map('AlreadyClaimedTaxExclusiveAmount')]
	private string $alreadyClaimedTaxExclusiveAmount;

	/** Total amount of all already claimed advance invoices without tax in a foreign currency. */
	#[Map('AlreadyClaimedTaxExclusiveAmountCurr')]
	private ?string $alreadyClaimedTaxExclusiveAmountCurr = null;

	/** Total amount of all already claimed advance invoices including tax in a local currency. */
	#[Map('AlreadyClaimedTaxInclusiveAmount')]
	private string $alreadyClaimedTaxInclusiveAmount;

	/** Total amount of all already claimed advance invoices including tax in a foreign currency. */
	#[Map('AlreadyClaimedTaxInclusiveAmountCurr')]
	private ?string $alreadyClaimedTaxInclusiveAmountCurr = null;

	/** Difference between precept and already claimed amount without tax in a local currency. */
	#[Map('DifferenceTaxExclusiveAmount')]
	private string $differenceTaxExclusiveAmount;

	/** Difference between precept and already claimed amount without tax in a foreign currency. */
	#[Map('DifferenceTaxExclusiveAmountCurr')]
	private ?string $differenceTaxExclusiveAmountCurr = null;

	/** Difference between precept and already claimed amount including tax in a local currency. */
	#[Map('DifferenceTaxInclusiveAmount')]
	private string $differenceTaxInclusiveAmount;

	/** Difference between precept and already claimed amount including tax in a foreign currency. */
	#[Map('DifferenceTaxInclusiveAmountCurr')]
	private ?string $differenceTaxInclusiveAmountCurr = null;

	/** Rounding of the total amount in a local currency. */
	#[Map('PayableRoundingAmount')]
	private ?string $payableRoundingAmount = null;

	/** Rounding of the total amount in a foreign currency. */
	#[Map('PayableRoundingAmountCurr')]
	private ?string $payableRoundingAmountCurr = null;

	/** Paid non-taxable deposit in a local currency. */
	#[Map('PaidDepositsAmount')]
	private string $paidDepositsAmount;

	/** Paid non-taxable deposit in a foreign currency. */
	#[Map('PaidDepositsAmountCurr')]
	private ?string $paidDepositsAmountCurr = null;

	/** Payable amount in a local currency. */
	#[Map('PayableAmount')]
	private string $payableAmount;

	/** Payable amount in a foreign currency. */
	#[Map('PayableAmountCurr')]
	private ?string $payableAmountCurr = null;

	public function __construct(
		string|BcMath\Number $taxExclusiveAmount,
		string|BcMath\Number $taxInclusiveAmount,
		string|BcMath\Number $alreadyClaimedTaxExclusiveAmount,
		string|BcMath\Number $alreadyClaimedTaxInclusiveAmount,
		string|BcMath\Number $differenceTaxExclusiveAmount,
		string|BcMath\Number $differenceTaxInclusiveAmount,
		string|BcMath\Number $paidDepositsAmount,
		string|BcMath\Number $payableAmount,
	) {
		$this->setTaxExclusiveAmount($taxExclusiveAmount);
		$this->setTaxInclusiveAmount($taxInclusiveAmount);
		$this->setAlreadyClaimedTaxExclusiveAmount($alreadyClaimedTaxExclusiveAmount);
		$this->setAlreadyClaimedTaxInclusiveAmount($alreadyClaimedTaxInclusiveAmount);
		$this->setDifferenceTaxExclusiveAmount($differenceTaxExclusiveAmount);
		$this->setDifferenceTaxInclusiveAmount($differenceTaxInclusiveAmount);
		$this->setPaidDepositsAmount($paidDepositsAmount);
		$this->setPayableAmount($payableAmount);
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxExclusiveAmount} property instead. */
	public function getTaxExclusiveAmount(): string
	{
		return $this->taxExclusiveAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxExclusiveAmount} property instead. */
	public function setTaxExclusiveAmount(string|BcMath\Number $taxExclusiveAmount): self
	{
		Deprecations::number($taxExclusiveAmount);
		$taxExclusiveAmount = (string) $taxExclusiveAmount;
		Restriction::decimal($taxExclusiveAmount);
		$this->taxExclusiveAmount = $taxExclusiveAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxExclusiveAmountCurr} property instead. */
	public function getTaxExclusiveAmountCurr(): ?string
	{
		return $this->taxExclusiveAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxExclusiveAmountCurr} property instead. */
	public function setTaxExclusiveAmountCurr(string|BcMath\Number|null $taxExclusiveAmountCurr): self
	{
		Deprecations::number($taxExclusiveAmountCurr);
		$taxExclusiveAmountCurr = $taxExclusiveAmountCurr === null ? null : (string) $taxExclusiveAmountCurr;
		Restriction::decimal($taxExclusiveAmountCurr);
		$this->taxExclusiveAmountCurr = $taxExclusiveAmountCurr;
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

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxExclusiveAmount} property instead. */
	public function getAlreadyClaimedTaxExclusiveAmount(): string
	{
		return $this->alreadyClaimedTaxExclusiveAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxExclusiveAmount} property instead. */
	public function setAlreadyClaimedTaxExclusiveAmount(string|BcMath\Number $alreadyClaimedTaxExclusiveAmount): self
	{
		Deprecations::number($alreadyClaimedTaxExclusiveAmount);
		$alreadyClaimedTaxExclusiveAmount = (string) $alreadyClaimedTaxExclusiveAmount;
		Restriction::decimal($alreadyClaimedTaxExclusiveAmount);
		$this->alreadyClaimedTaxExclusiveAmount = $alreadyClaimedTaxExclusiveAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxExclusiveAmountCurr} property instead. */
	public function getAlreadyClaimedTaxExclusiveAmountCurr(): ?string
	{
		return $this->alreadyClaimedTaxExclusiveAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $alreadyClaimedTaxExclusiveAmountCurr} property instead. */
	public function setAlreadyClaimedTaxExclusiveAmountCurr(
		string|BcMath\Number|null $alreadyClaimedTaxExclusiveAmountCurr,
	): self
	{
		Deprecations::number($alreadyClaimedTaxExclusiveAmountCurr);
		$alreadyClaimedTaxExclusiveAmountCurr = $alreadyClaimedTaxExclusiveAmountCurr === null ? null : (string) $alreadyClaimedTaxExclusiveAmountCurr;
		Restriction::decimal($alreadyClaimedTaxExclusiveAmountCurr);
		$this->alreadyClaimedTaxExclusiveAmountCurr = $alreadyClaimedTaxExclusiveAmountCurr;
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

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxExclusiveAmount} property instead. */
	public function getDifferenceTaxExclusiveAmount(): string
	{
		return $this->differenceTaxExclusiveAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxExclusiveAmount} property instead. */
	public function setDifferenceTaxExclusiveAmount(string|BcMath\Number $differenceTaxExclusiveAmount): self
	{
		Deprecations::number($differenceTaxExclusiveAmount);
		$differenceTaxExclusiveAmount = (string) $differenceTaxExclusiveAmount;
		Restriction::decimal($differenceTaxExclusiveAmount);
		$this->differenceTaxExclusiveAmount = $differenceTaxExclusiveAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxExclusiveAmountCurr} property instead. */
	public function getDifferenceTaxExclusiveAmountCurr(): ?string
	{
		return $this->differenceTaxExclusiveAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $differenceTaxExclusiveAmountCurr} property instead. */
	public function setDifferenceTaxExclusiveAmountCurr(
		string|BcMath\Number|null $differenceTaxExclusiveAmountCurr,
	): self
	{
		Deprecations::number($differenceTaxExclusiveAmountCurr);
		$differenceTaxExclusiveAmountCurr = $differenceTaxExclusiveAmountCurr === null ? null : (string) $differenceTaxExclusiveAmountCurr;
		Restriction::decimal($differenceTaxExclusiveAmountCurr);
		$this->differenceTaxExclusiveAmountCurr = $differenceTaxExclusiveAmountCurr;
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

	/** @deprecated Method accessors are deprecated, use {@see $payableRoundingAmount} property instead. */
	public function getPayableRoundingAmount(): ?string
	{
		return $this->payableRoundingAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $payableRoundingAmount} property instead. */
	public function setPayableRoundingAmount(string|BcMath\Number|null $payableRoundingAmount): self
	{
		Deprecations::number($payableRoundingAmount);
		$payableRoundingAmount = $payableRoundingAmount === null ? null : (string) $payableRoundingAmount;
		Restriction::decimal($payableRoundingAmount);
		$this->payableRoundingAmount = $payableRoundingAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $payableRoundingAmountCurr} property instead. */
	public function getPayableRoundingAmountCurr(): ?string
	{
		return $this->payableRoundingAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $payableRoundingAmountCurr} property instead. */
	public function setPayableRoundingAmountCurr(string|BcMath\Number|null $payableRoundingAmountCurr): self
	{
		Deprecations::number($payableRoundingAmountCurr);
		$payableRoundingAmountCurr = $payableRoundingAmountCurr === null ? null : (string) $payableRoundingAmountCurr;
		Restriction::decimal($payableRoundingAmountCurr);
		$this->payableRoundingAmountCurr = $payableRoundingAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paidDepositsAmount} property instead. */
	public function getPaidDepositsAmount(): string
	{
		return $this->paidDepositsAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paidDepositsAmount} property instead. */
	public function setPaidDepositsAmount(string|BcMath\Number $paidDepositsAmount): self
	{
		Deprecations::number($paidDepositsAmount);
		$paidDepositsAmount = (string) $paidDepositsAmount;
		Restriction::decimal($paidDepositsAmount);
		$this->paidDepositsAmount = $paidDepositsAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paidDepositsAmountCurr} property instead. */
	public function getPaidDepositsAmountCurr(): ?string
	{
		return $this->paidDepositsAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paidDepositsAmountCurr} property instead. */
	public function setPaidDepositsAmountCurr(string|BcMath\Number|null $paidDepositsAmountCurr): self
	{
		Deprecations::number($paidDepositsAmountCurr);
		$paidDepositsAmountCurr = $paidDepositsAmountCurr === null ? null : (string) $paidDepositsAmountCurr;
		Restriction::decimal($paidDepositsAmountCurr);
		$this->paidDepositsAmountCurr = $paidDepositsAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $payableAmount} property instead. */
	public function getPayableAmount(): string
	{
		return $this->payableAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $payableAmount} property instead. */
	public function setPayableAmount(string|BcMath\Number $payableAmount): self
	{
		Deprecations::number($payableAmount);
		$payableAmount = (string) $payableAmount;
		Restriction::decimal($payableAmount);
		$this->payableAmount = $payableAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $payableAmountCurr} property instead. */
	public function getPayableAmountCurr(): ?string
	{
		return $this->payableAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $payableAmountCurr} property instead. */
	public function setPayableAmountCurr(string|BcMath\Number|null $payableAmountCurr): self
	{
		Deprecations::number($payableAmountCurr);
		$payableAmountCurr = $payableAmountCurr === null ? null : (string) $payableAmountCurr;
		Restriction::decimal($payableAmountCurr);
		$this->payableAmountCurr = $payableAmountCurr;
		return $this;
	}

}