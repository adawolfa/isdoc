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
 * Information about amount and rate on taxed deposit (advance invoice).
 *
 * @property string                    $id
 * @property string                    $variableSymbol
 * @property string|BcMath\Number|null $taxableDepositAmountCurr
 * @property string|BcMath\Number      $taxableDepositAmount
 * @property string|BcMath\Number|null $taxInclusiveDepositAmountCurr
 * @property string|BcMath\Number      $taxInclusiveDepositAmount
 * @property ClassifiedTaxCategory     $classifiedTaxCategory
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
		string|BcMath\Number $taxableDepositAmount,
		string|BcMath\Number $taxInclusiveDepositAmount,
		ClassifiedTaxCategory $classifiedTaxCategory,
	) {
		$this->setId($id);
		$this->setVariableSymbol($variableSymbol);
		$this->setTaxableDepositAmount($taxableDepositAmount);
		$this->setTaxInclusiveDepositAmount($taxInclusiveDepositAmount);
		$this->setClassifiedTaxCategory($classifiedTaxCategory);
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function getId(): string
	{
		return $this->id;
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function setId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $variableSymbol} property instead. */
	public function getVariableSymbol(): string
	{
		return $this->variableSymbol;
	}

	/** @deprecated Method accessors are deprecated, use {@see $variableSymbol} property instead. */
	public function setVariableSymbol(string $variableSymbol): self
	{
		$this->variableSymbol = $variableSymbol;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxableDepositAmountCurr} property instead. */
	public function getTaxableDepositAmountCurr(): ?string
	{
		return $this->taxableDepositAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxableDepositAmountCurr} property instead. */
	public function setTaxableDepositAmountCurr(string|BcMath\Number|null $taxableDepositAmountCurr): self
	{
		Deprecations::number($taxableDepositAmountCurr);
		$taxableDepositAmountCurr = $taxableDepositAmountCurr === null ? null : (string) $taxableDepositAmountCurr;
		Restriction::decimal($taxableDepositAmountCurr);
		$this->taxableDepositAmountCurr = $taxableDepositAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxableDepositAmount} property instead. */
	public function getTaxableDepositAmount(): string
	{
		return $this->taxableDepositAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxableDepositAmount} property instead. */
	public function setTaxableDepositAmount(string|BcMath\Number $taxableDepositAmount): self
	{
		Deprecations::number($taxableDepositAmount);
		$taxableDepositAmount = (string) $taxableDepositAmount;
		Restriction::decimal($taxableDepositAmount);
		$this->taxableDepositAmount = $taxableDepositAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxInclusiveDepositAmountCurr} property instead. */
	public function getTaxInclusiveDepositAmountCurr(): ?string
	{
		return $this->taxInclusiveDepositAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxInclusiveDepositAmountCurr} property instead. */
	public function setTaxInclusiveDepositAmountCurr(string|BcMath\Number|null $taxInclusiveDepositAmountCurr): self
	{
		Deprecations::number($taxInclusiveDepositAmountCurr);
		$taxInclusiveDepositAmountCurr = $taxInclusiveDepositAmountCurr === null ? null : (string) $taxInclusiveDepositAmountCurr;
		Restriction::decimal($taxInclusiveDepositAmountCurr);
		$this->taxInclusiveDepositAmountCurr = $taxInclusiveDepositAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxInclusiveDepositAmount} property instead. */
	public function getTaxInclusiveDepositAmount(): string
	{
		return $this->taxInclusiveDepositAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxInclusiveDepositAmount} property instead. */
	public function setTaxInclusiveDepositAmount(string|BcMath\Number $taxInclusiveDepositAmount): self
	{
		Deprecations::number($taxInclusiveDepositAmount);
		$taxInclusiveDepositAmount = (string) $taxInclusiveDepositAmount;
		Restriction::decimal($taxInclusiveDepositAmount);
		$this->taxInclusiveDepositAmount = $taxInclusiveDepositAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $classifiedTaxCategory} property instead. */
	public function getClassifiedTaxCategory(): ClassifiedTaxCategory
	{
		return $this->classifiedTaxCategory;
	}

	/** @deprecated Method accessors are deprecated, use {@see $classifiedTaxCategory} property instead. */
	public function setClassifiedTaxCategory(ClassifiedTaxCategory $classifiedTaxCategory): self
	{
		$this->classifiedTaxCategory = $classifiedTaxCategory;
		return $this;
	}

}