<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Collection of total amounts on document ending with payable amount.
 *
 * @property string      $taxExclusiveAmount
 * @property string|null $taxExclusiveAmountCurr
 * @property string      $taxInclusiveAmount
 * @property string|null $taxInclusiveAmountCurr
 * @property string      $alreadyClaimedTaxExclusiveAmount
 * @property string|null $alreadyClaimedTaxExclusiveAmountCurr
 * @property string      $alreadyClaimedTaxInclusiveAmount
 * @property string|null $alreadyClaimedTaxInclusiveAmountCurr
 * @property string      $differenceTaxExclusiveAmount
 * @property string|null $differenceTaxExclusiveAmountCurr
 * @property string      $differenceTaxInclusiveAmount
 * @property string|null $differenceTaxInclusiveAmountCurr
 * @property string|null $payableRoundingAmount
 * @property string|null $payableRoundingAmountCurr
 * @property string      $paidDepositsAmount
 * @property string|null $paidDepositsAmountCurr
 * @property string      $payableAmount
 * @property string|null $payableAmountCurr
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
		string $taxExclusiveAmount,
		string $taxInclusiveAmount,
		string $alreadyClaimedTaxExclusiveAmount,
		string $alreadyClaimedTaxInclusiveAmount,
		string $differenceTaxExclusiveAmount,
		string $differenceTaxInclusiveAmount,
		string $paidDepositsAmount,
		string $payableAmount
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

	public function getTaxExclusiveAmount(): string
	{
		return $this->taxExclusiveAmount;
	}

	public function setTaxExclusiveAmount(string $taxExclusiveAmount): self
	{
		Restriction::decimal($taxExclusiveAmount);
		$this->taxExclusiveAmount = $taxExclusiveAmount;
		return $this;
	}

	public function getTaxExclusiveAmountCurr(): ?string
	{
		return $this->taxExclusiveAmountCurr;
	}

	public function setTaxExclusiveAmountCurr(?string $taxExclusiveAmountCurr): self
	{
		Restriction::decimal($taxExclusiveAmountCurr);
		$this->taxExclusiveAmountCurr = $taxExclusiveAmountCurr;
		return $this;
	}

	public function getTaxInclusiveAmount(): string
	{
		return $this->taxInclusiveAmount;
	}

	public function setTaxInclusiveAmount(string $taxInclusiveAmount): self
	{
		Restriction::decimal($taxInclusiveAmount);
		$this->taxInclusiveAmount = $taxInclusiveAmount;
		return $this;
	}

	public function getTaxInclusiveAmountCurr(): ?string
	{
		return $this->taxInclusiveAmountCurr;
	}

	public function setTaxInclusiveAmountCurr(?string $taxInclusiveAmountCurr): self
	{
		Restriction::decimal($taxInclusiveAmountCurr);
		$this->taxInclusiveAmountCurr = $taxInclusiveAmountCurr;
		return $this;
	}

	public function getAlreadyClaimedTaxExclusiveAmount(): string
	{
		return $this->alreadyClaimedTaxExclusiveAmount;
	}

	public function setAlreadyClaimedTaxExclusiveAmount(string $alreadyClaimedTaxExclusiveAmount): self
	{
		Restriction::decimal($alreadyClaimedTaxExclusiveAmount);
		$this->alreadyClaimedTaxExclusiveAmount = $alreadyClaimedTaxExclusiveAmount;
		return $this;
	}

	public function getAlreadyClaimedTaxExclusiveAmountCurr(): ?string
	{
		return $this->alreadyClaimedTaxExclusiveAmountCurr;
	}

	public function setAlreadyClaimedTaxExclusiveAmountCurr(?string $alreadyClaimedTaxExclusiveAmountCurr): self
	{
		Restriction::decimal($alreadyClaimedTaxExclusiveAmountCurr);
		$this->alreadyClaimedTaxExclusiveAmountCurr = $alreadyClaimedTaxExclusiveAmountCurr;
		return $this;
	}

	public function getAlreadyClaimedTaxInclusiveAmount(): string
	{
		return $this->alreadyClaimedTaxInclusiveAmount;
	}

	public function setAlreadyClaimedTaxInclusiveAmount(string $alreadyClaimedTaxInclusiveAmount): self
	{
		Restriction::decimal($alreadyClaimedTaxInclusiveAmount);
		$this->alreadyClaimedTaxInclusiveAmount = $alreadyClaimedTaxInclusiveAmount;
		return $this;
	}

	public function getAlreadyClaimedTaxInclusiveAmountCurr(): ?string
	{
		return $this->alreadyClaimedTaxInclusiveAmountCurr;
	}

	public function setAlreadyClaimedTaxInclusiveAmountCurr(?string $alreadyClaimedTaxInclusiveAmountCurr): self
	{
		Restriction::decimal($alreadyClaimedTaxInclusiveAmountCurr);
		$this->alreadyClaimedTaxInclusiveAmountCurr = $alreadyClaimedTaxInclusiveAmountCurr;
		return $this;
	}

	public function getDifferenceTaxExclusiveAmount(): string
	{
		return $this->differenceTaxExclusiveAmount;
	}

	public function setDifferenceTaxExclusiveAmount(string $differenceTaxExclusiveAmount): self
	{
		Restriction::decimal($differenceTaxExclusiveAmount);
		$this->differenceTaxExclusiveAmount = $differenceTaxExclusiveAmount;
		return $this;
	}

	public function getDifferenceTaxExclusiveAmountCurr(): ?string
	{
		return $this->differenceTaxExclusiveAmountCurr;
	}

	public function setDifferenceTaxExclusiveAmountCurr(?string $differenceTaxExclusiveAmountCurr): self
	{
		Restriction::decimal($differenceTaxExclusiveAmountCurr);
		$this->differenceTaxExclusiveAmountCurr = $differenceTaxExclusiveAmountCurr;
		return $this;
	}

	public function getDifferenceTaxInclusiveAmount(): string
	{
		return $this->differenceTaxInclusiveAmount;
	}

	public function setDifferenceTaxInclusiveAmount(string $differenceTaxInclusiveAmount): self
	{
		Restriction::decimal($differenceTaxInclusiveAmount);
		$this->differenceTaxInclusiveAmount = $differenceTaxInclusiveAmount;
		return $this;
	}

	public function getDifferenceTaxInclusiveAmountCurr(): ?string
	{
		return $this->differenceTaxInclusiveAmountCurr;
	}

	public function setDifferenceTaxInclusiveAmountCurr(?string $differenceTaxInclusiveAmountCurr): self
	{
		Restriction::decimal($differenceTaxInclusiveAmountCurr);
		$this->differenceTaxInclusiveAmountCurr = $differenceTaxInclusiveAmountCurr;
		return $this;
	}

	public function getPayableRoundingAmount(): ?string
	{
		return $this->payableRoundingAmount;
	}

	public function setPayableRoundingAmount(?string $payableRoundingAmount): self
	{
		Restriction::decimal($payableRoundingAmount);
		$this->payableRoundingAmount = $payableRoundingAmount;
		return $this;
	}

	public function getPayableRoundingAmountCurr(): ?string
	{
		return $this->payableRoundingAmountCurr;
	}

	public function setPayableRoundingAmountCurr(?string $payableRoundingAmountCurr): self
	{
		Restriction::decimal($payableRoundingAmountCurr);
		$this->payableRoundingAmountCurr = $payableRoundingAmountCurr;
		return $this;
	}

	public function getPaidDepositsAmount(): string
	{
		return $this->paidDepositsAmount;
	}

	public function setPaidDepositsAmount(string $paidDepositsAmount): self
	{
		Restriction::decimal($paidDepositsAmount);
		$this->paidDepositsAmount = $paidDepositsAmount;
		return $this;
	}

	public function getPaidDepositsAmountCurr(): ?string
	{
		return $this->paidDepositsAmountCurr;
	}

	public function setPaidDepositsAmountCurr(?string $paidDepositsAmountCurr): self
	{
		Restriction::decimal($paidDepositsAmountCurr);
		$this->paidDepositsAmountCurr = $paidDepositsAmountCurr;
		return $this;
	}

	public function getPayableAmount(): string
	{
		return $this->payableAmount;
	}

	public function setPayableAmount(string $payableAmount): self
	{
		Restriction::decimal($payableAmount);
		$this->payableAmount = $payableAmount;
		return $this;
	}

	public function getPayableAmountCurr(): ?string
	{
		return $this->payableAmountCurr;
	}

	public function setPayableAmountCurr(?string $payableAmountCurr): self
	{
		Restriction::decimal($payableAmountCurr);
		$this->payableAmountCurr = $payableAmountCurr;
		return $this;
	}

}