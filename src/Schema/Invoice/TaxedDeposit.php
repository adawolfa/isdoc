<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about amount and rate on taxed deposit (advance invoice).
 *
 * @property string                $id
 * @property string                $variableSymbol
 * @property string|null           $taxableDepositAmountCurr
 * @property string                $taxableDepositAmount
 * @property string|null           $taxInclusiveDepositAmountCurr
 * @property string                $taxInclusiveDepositAmount
 * @property ClassifiedTaxCategory $classifiedTaxCategory
 */
class TaxedDeposit implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Document name, issuer identification of taxed advance invoice. */
	#[Map('ID')]
	private string $id;

	/** Variable symbol (distinctive symbol of payment, typically number of invoice). Used for payment inside of the Czech Republic. */
	#[Map('VariableSymbol')]
	private string $variableSymbol;

	/** Deposit amount without tax in a foreign currency. */
	#[Map('TaxableDepositAmountCurr')]
	private ?string $taxableDepositAmountCurr = null;

	/** Deposit amount without tax in a local currency. */
	#[Map('TaxableDepositAmount')]
	private string $taxableDepositAmount;

	/** Deposit amount including tax in a foreign currency. */
	#[Map('TaxInclusiveDepositAmountCurr')]
	private ?string $taxInclusiveDepositAmountCurr = null;

	/** Deposit amount including tax in a local currency. */
	#[Map('TaxInclusiveDepositAmount')]
	private string $taxInclusiveDepositAmount;

	/** Compound VAT field. */
	#[Map('ClassifiedTaxCategory')]
	private ClassifiedTaxCategory $classifiedTaxCategory;

	public function __construct(
		string $id,
		string $variableSymbol,
		string $taxableDepositAmount,
		string $taxInclusiveDepositAmount,
		ClassifiedTaxCategory $classifiedTaxCategory
	) {
		$this->setId($id);
		$this->setVariableSymbol($variableSymbol);
		$this->setTaxableDepositAmount($taxableDepositAmount);
		$this->setTaxInclusiveDepositAmount($taxInclusiveDepositAmount);
		$this->setClassifiedTaxCategory($classifiedTaxCategory);
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	public function getVariableSymbol(): string
	{
		return $this->variableSymbol;
	}

	public function setVariableSymbol(string $variableSymbol): self
	{
		$this->variableSymbol = $variableSymbol;
		return $this;
	}

	public function getTaxableDepositAmountCurr(): ?string
	{
		return $this->taxableDepositAmountCurr;
	}

	public function setTaxableDepositAmountCurr(?string $taxableDepositAmountCurr): self
	{
		Restriction::decimal($taxableDepositAmountCurr);
		$this->taxableDepositAmountCurr = $taxableDepositAmountCurr;
		return $this;
	}

	public function getTaxableDepositAmount(): string
	{
		return $this->taxableDepositAmount;
	}

	public function setTaxableDepositAmount(string $taxableDepositAmount): self
	{
		Restriction::decimal($taxableDepositAmount);
		$this->taxableDepositAmount = $taxableDepositAmount;
		return $this;
	}

	public function getTaxInclusiveDepositAmountCurr(): ?string
	{
		return $this->taxInclusiveDepositAmountCurr;
	}

	public function setTaxInclusiveDepositAmountCurr(?string $taxInclusiveDepositAmountCurr): self
	{
		Restriction::decimal($taxInclusiveDepositAmountCurr);
		$this->taxInclusiveDepositAmountCurr = $taxInclusiveDepositAmountCurr;
		return $this;
	}

	public function getTaxInclusiveDepositAmount(): string
	{
		return $this->taxInclusiveDepositAmount;
	}

	public function setTaxInclusiveDepositAmount(string $taxInclusiveDepositAmount): self
	{
		Restriction::decimal($taxInclusiveDepositAmount);
		$this->taxInclusiveDepositAmount = $taxInclusiveDepositAmount;
		return $this;
	}

	public function getClassifiedTaxCategory(): ClassifiedTaxCategory
	{
		return $this->classifiedTaxCategory;
	}

	public function setClassifiedTaxCategory(ClassifiedTaxCategory $classifiedTaxCategory): self
	{
		$this->classifiedTaxCategory = $classifiedTaxCategory;
		return $this;
	}

}