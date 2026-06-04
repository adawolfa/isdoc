<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;

/**
 * Subtotals for one tax rate.
 */
class TaxSubTotal implements Entity
{

	use Backing;

	/** Tax base for rate in a foreign currency. */
	public ?Number $taxableAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('TaxableAmountCurr');
		set {
			$this->node->setNumber('TaxableAmountCurr', $value);
		}
	}

	/** Tax base for rate in a local currency. */
	public Number $taxableAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('TaxableAmount');
		set {
			$this->node->setNumber('TaxableAmount', $value);
		}
	}

	/** Tax for rate in a foreign currency. */
	public ?Number $taxAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('TaxAmountCurr');
		set {
			$this->node->setNumber('TaxAmountCurr', $value);
		}
	}

	/** Tax for rate in a local currency. */
	public Number $taxAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('TaxAmount');
		set {
			$this->node->setNumber('TaxAmount', $value);
		}
	}

	/** Amount including tax for rate in a foreign currency. */
	public ?Number $taxInclusiveAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('TaxInclusiveAmountCurr');
		set {
			$this->node->setNumber('TaxInclusiveAmountCurr', $value);
		}
	}

	/** Amount including tax for rate in a local currency. */
	public Number $taxInclusiveAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('TaxInclusiveAmount');
		set {
			$this->node->setNumber('TaxInclusiveAmount', $value);
		}
	}

	/** Already claimed amount for rate in a foreign currency. */
	public ?Number $alreadyClaimedTaxableAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('AlreadyClaimedTaxableAmountCurr');
		set {
			$this->node->setNumber('AlreadyClaimedTaxableAmountCurr', $value);
		}
	}

	/** Already claimed amount for rate in a local currency. */
	public Number $alreadyClaimedTaxableAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('AlreadyClaimedTaxableAmount');
		set {
			$this->node->setNumber('AlreadyClaimedTaxableAmount', $value);
		}
	}

	/** Already claimed tax for rate in a foreign currency. */
	public ?Number $alreadyClaimedTaxAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('AlreadyClaimedTaxAmountCurr');
		set {
			$this->node->setNumber('AlreadyClaimedTaxAmountCurr', $value);
		}
	}

	/** Already claimed tax for rate in a local currency. */
	public Number $alreadyClaimedTaxAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('AlreadyClaimedTaxAmount');
		set {
			$this->node->setNumber('AlreadyClaimedTaxAmount', $value);
		}
	}

	/** Already claimed amount including tax for rate in a foreign currency. */
	public ?Number $alreadyClaimedTaxInclusiveAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('AlreadyClaimedTaxInclusiveAmountCurr');
		set {
			$this->node->setNumber('AlreadyClaimedTaxInclusiveAmountCurr', $value);
		}
	}

	/** Already claimed amount including tax for rate in a local currency. */
	public Number $alreadyClaimedTaxInclusiveAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('AlreadyClaimedTaxInclusiveAmount');
		set {
			$this->node->setNumber('AlreadyClaimedTaxInclusiveAmount', $value);
		}
	}

	/** Difference in the amount in a foreign currency. */
	public ?Number $differenceTaxableAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('DifferenceTaxableAmountCurr');
		set {
			$this->node->setNumber('DifferenceTaxableAmountCurr', $value);
		}
	}

	/** Difference in the amount in a local currency. */
	public Number $differenceTaxableAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('DifferenceTaxableAmount');
		set {
			$this->node->setNumber('DifferenceTaxableAmount', $value);
		}
	}

	/** Difference in the tax in a foreign currency. */
	public ?Number $differenceTaxAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('DifferenceTaxAmountCurr');
		set {
			$this->node->setNumber('DifferenceTaxAmountCurr', $value);
		}
	}

	/** Difference in the tax in a local currency. */
	public Number $differenceTaxAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('DifferenceTaxAmount');
		set {
			$this->node->setNumber('DifferenceTaxAmount', $value);
		}
	}

	/** Difference including tax in a foreign currency. */
	public ?Number $differenceTaxInclusiveAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('DifferenceTaxInclusiveAmountCurr');
		set {
			$this->node->setNumber('DifferenceTaxInclusiveAmountCurr', $value);
		}
	}

	/** Difference including tax in a local currency. */
	public Number $differenceTaxInclusiveAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('DifferenceTaxInclusiveAmount');
		set {
			$this->node->setNumber('DifferenceTaxInclusiveAmount', $value);
		}
	}

	/** Information about a tax rate. */
	public TaxCategory $taxCategory {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('TaxCategory', TaxCategory::class);
		set { $this->node->setChild('TaxCategory', $value); }
	}

	public function __construct(
		Number $taxableAmount,
		Number $taxAmount,
		Number $taxInclusiveAmount,
		Number $alreadyClaimedTaxableAmount,
		Number $alreadyClaimedTaxAmount,
		Number $alreadyClaimedTaxInclusiveAmount,
		Number $differenceTaxableAmount,
		Number $differenceTaxAmount,
		Number $differenceTaxInclusiveAmount,
		TaxCategory $taxCategory,
	)
	{
		$this->taxableAmount = $taxableAmount;
		$this->taxAmount = $taxAmount;
		$this->taxInclusiveAmount = $taxInclusiveAmount;
		$this->alreadyClaimedTaxableAmount = $alreadyClaimedTaxableAmount;
		$this->alreadyClaimedTaxAmount = $alreadyClaimedTaxAmount;
		$this->alreadyClaimedTaxInclusiveAmount = $alreadyClaimedTaxInclusiveAmount;
		$this->differenceTaxableAmount = $differenceTaxableAmount;
		$this->differenceTaxAmount = $differenceTaxAmount;
		$this->differenceTaxInclusiveAmount = $differenceTaxInclusiveAmount;
		$this->taxCategory = $taxCategory;
	}

}