<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Subtotals for one tax rate.
 *
 * @property string|null $taxableAmountCurr
 * @property string      $taxableAmount
 * @property string|null $taxAmountCurr
 * @property string      $taxAmount
 * @property string|null $taxInclusiveAmountCurr
 * @property string      $taxInclusiveAmount
 * @property string|null $alreadyClaimedTaxableAmountCurr
 * @property string      $alreadyClaimedTaxableAmount
 * @property string|null $alreadyClaimedTaxAmountCurr
 * @property string      $alreadyClaimedTaxAmount
 * @property string|null $alreadyClaimedTaxInclusiveAmountCurr
 * @property string      $alreadyClaimedTaxInclusiveAmount
 * @property string|null $differenceTaxableAmountCurr
 * @property string      $differenceTaxableAmount
 * @property string|null $differenceTaxAmountCurr
 * @property string      $differenceTaxAmount
 * @property string|null $differenceTaxInclusiveAmountCurr
 * @property string      $differenceTaxInclusiveAmount
 * @property TaxCategory $taxCategory
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
		string $taxableAmount,
		string $taxAmount,
		string $taxInclusiveAmount,
		string $alreadyClaimedTaxableAmount,
		string $alreadyClaimedTaxAmount,
		string $alreadyClaimedTaxInclusiveAmount,
		string $differenceTaxableAmount,
		string $differenceTaxAmount,
		string $differenceTaxInclusiveAmount,
		TaxCategory $taxCategory
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

	public function getTaxableAmountCurr(): ?string
	{
		return $this->taxableAmountCurr;
	}

	public function setTaxableAmountCurr(?string $taxableAmountCurr): self
	{
		Restriction::decimal($taxableAmountCurr);
		$this->taxableAmountCurr = $taxableAmountCurr;
		return $this;
	}

	public function getTaxableAmount(): string
	{
		return $this->taxableAmount;
	}

	public function setTaxableAmount(string $taxableAmount): self
	{
		Restriction::decimal($taxableAmount);
		$this->taxableAmount = $taxableAmount;
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

	public function getAlreadyClaimedTaxableAmountCurr(): ?string
	{
		return $this->alreadyClaimedTaxableAmountCurr;
	}

	public function setAlreadyClaimedTaxableAmountCurr(?string $alreadyClaimedTaxableAmountCurr): self
	{
		Restriction::decimal($alreadyClaimedTaxableAmountCurr);
		$this->alreadyClaimedTaxableAmountCurr = $alreadyClaimedTaxableAmountCurr;
		return $this;
	}

	public function getAlreadyClaimedTaxableAmount(): string
	{
		return $this->alreadyClaimedTaxableAmount;
	}

	public function setAlreadyClaimedTaxableAmount(string $alreadyClaimedTaxableAmount): self
	{
		Restriction::decimal($alreadyClaimedTaxableAmount);
		$this->alreadyClaimedTaxableAmount = $alreadyClaimedTaxableAmount;
		return $this;
	}

	public function getAlreadyClaimedTaxAmountCurr(): ?string
	{
		return $this->alreadyClaimedTaxAmountCurr;
	}

	public function setAlreadyClaimedTaxAmountCurr(?string $alreadyClaimedTaxAmountCurr): self
	{
		Restriction::decimal($alreadyClaimedTaxAmountCurr);
		$this->alreadyClaimedTaxAmountCurr = $alreadyClaimedTaxAmountCurr;
		return $this;
	}

	public function getAlreadyClaimedTaxAmount(): string
	{
		return $this->alreadyClaimedTaxAmount;
	}

	public function setAlreadyClaimedTaxAmount(string $alreadyClaimedTaxAmount): self
	{
		Restriction::decimal($alreadyClaimedTaxAmount);
		$this->alreadyClaimedTaxAmount = $alreadyClaimedTaxAmount;
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

	public function getDifferenceTaxableAmountCurr(): ?string
	{
		return $this->differenceTaxableAmountCurr;
	}

	public function setDifferenceTaxableAmountCurr(?string $differenceTaxableAmountCurr): self
	{
		Restriction::decimal($differenceTaxableAmountCurr);
		$this->differenceTaxableAmountCurr = $differenceTaxableAmountCurr;
		return $this;
	}

	public function getDifferenceTaxableAmount(): string
	{
		return $this->differenceTaxableAmount;
	}

	public function setDifferenceTaxableAmount(string $differenceTaxableAmount): self
	{
		Restriction::decimal($differenceTaxableAmount);
		$this->differenceTaxableAmount = $differenceTaxableAmount;
		return $this;
	}

	public function getDifferenceTaxAmountCurr(): ?string
	{
		return $this->differenceTaxAmountCurr;
	}

	public function setDifferenceTaxAmountCurr(?string $differenceTaxAmountCurr): self
	{
		Restriction::decimal($differenceTaxAmountCurr);
		$this->differenceTaxAmountCurr = $differenceTaxAmountCurr;
		return $this;
	}

	public function getDifferenceTaxAmount(): string
	{
		return $this->differenceTaxAmount;
	}

	public function setDifferenceTaxAmount(string $differenceTaxAmount): self
	{
		Restriction::decimal($differenceTaxAmount);
		$this->differenceTaxAmount = $differenceTaxAmount;
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

	public function getTaxCategory(): TaxCategory
	{
		return $this->taxCategory;
	}

	public function setTaxCategory(TaxCategory $taxCategory): self
	{
		$this->taxCategory = $taxCategory;
		return $this;
	}

}