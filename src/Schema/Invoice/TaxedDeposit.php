<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;

/**
 * Information about amount and rate on taxed deposit (advance invoice).
 */
class TaxedDeposit implements Entity
{

	use Backing;

	/** Document name, issuer identification of taxed advance invoice. */
	public string $id {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('ID');
		set {
			$this->node->setString('ID', $value);
		}
	}

	/** Variable symbol (distinctive symbol of payment, typically number of invoice). Used for payment inside of the Czech Republic. */
	public string $variableSymbol {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('VariableSymbol');
		set {
			$this->node->setString('VariableSymbol', $value);
		}
	}

	/** Deposit amount without tax in a foreign currency. */
	public ?Number $taxableDepositAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('TaxableDepositAmountCurr');
		set {
			$this->node->setNumber('TaxableDepositAmountCurr', $value);
		}
	}

	/** Deposit amount without tax in a local currency. */
	public Number $taxableDepositAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('TaxableDepositAmount');
		set {
			$this->node->setNumber('TaxableDepositAmount', $value);
		}
	}

	/** Deposit amount including tax in a foreign currency. */
	public ?Number $taxInclusiveDepositAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('TaxInclusiveDepositAmountCurr');
		set {
			$this->node->setNumber('TaxInclusiveDepositAmountCurr', $value);
		}
	}

	/** Deposit amount including tax in a local currency. */
	public Number $taxInclusiveDepositAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('TaxInclusiveDepositAmount');
		set {
			$this->node->setNumber('TaxInclusiveDepositAmount', $value);
		}
	}

	/** Compound VAT field. */
	public ClassifiedTaxCategory $classifiedTaxCategory {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('ClassifiedTaxCategory', ClassifiedTaxCategory::class);
		set { $this->node->setChild('ClassifiedTaxCategory', $value); }
	}

	public function __construct(
		string $id,
		string $variableSymbol,
		Number $taxableDepositAmount,
		Number $taxInclusiveDepositAmount,
		ClassifiedTaxCategory $classifiedTaxCategory,
	)
	{
		$this->id = $id;
		$this->variableSymbol = $variableSymbol;
		$this->taxableDepositAmount = $taxableDepositAmount;
		$this->taxInclusiveDepositAmount = $taxInclusiveDepositAmount;
		$this->classifiedTaxCategory = $classifiedTaxCategory;
	}

}