<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;

/**
 * Collection of total amounts on document ending with payable amount.
 */
class LegalMonetaryTotal implements Entity
{

	use Backing;

	/** Total amount without tax in a local currency. */
	public Number $taxExclusiveAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('TaxExclusiveAmount');
		set {
			$this->node->setNumber('TaxExclusiveAmount', $value);
		}
	}

	/** Total amount without tax in a foreign currency. */
	public ?Number $taxExclusiveAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('TaxExclusiveAmountCurr');
		set {
			$this->node->setNumber('TaxExclusiveAmountCurr', $value);
		}
	}

	/** Total amount including tax in a local currency. */
	public Number $taxInclusiveAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('TaxInclusiveAmount');
		set {
			$this->node->setNumber('TaxInclusiveAmount', $value);
		}
	}

	/** Total amount including tax in a foreign currency. */
	public ?Number $taxInclusiveAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('TaxInclusiveAmountCurr');
		set {
			$this->node->setNumber('TaxInclusiveAmountCurr', $value);
		}
	}

	/** Total amount of all already claimed advance invoices without tax in a local currency. */
	public Number $alreadyClaimedTaxExclusiveAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('AlreadyClaimedTaxExclusiveAmount');
		set {
			$this->node->setNumber('AlreadyClaimedTaxExclusiveAmount', $value);
		}
	}

	/** Total amount of all already claimed advance invoices without tax in a foreign currency. */
	public ?Number $alreadyClaimedTaxExclusiveAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('AlreadyClaimedTaxExclusiveAmountCurr');
		set {
			$this->node->setNumber('AlreadyClaimedTaxExclusiveAmountCurr', $value);
		}
	}

	/** Total amount of all already claimed advance invoices including tax in a local currency. */
	public Number $alreadyClaimedTaxInclusiveAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('AlreadyClaimedTaxInclusiveAmount');
		set {
			$this->node->setNumber('AlreadyClaimedTaxInclusiveAmount', $value);
		}
	}

	/** Total amount of all already claimed advance invoices including tax in a foreign currency. */
	public ?Number $alreadyClaimedTaxInclusiveAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('AlreadyClaimedTaxInclusiveAmountCurr');
		set {
			$this->node->setNumber('AlreadyClaimedTaxInclusiveAmountCurr', $value);
		}
	}

	/** Difference between precept and already claimed amount without tax in a local currency. */
	public Number $differenceTaxExclusiveAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('DifferenceTaxExclusiveAmount');
		set {
			$this->node->setNumber('DifferenceTaxExclusiveAmount', $value);
		}
	}

	/** Difference between precept and already claimed amount without tax in a foreign currency. */
	public ?Number $differenceTaxExclusiveAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('DifferenceTaxExclusiveAmountCurr');
		set {
			$this->node->setNumber('DifferenceTaxExclusiveAmountCurr', $value);
		}
	}

	/** Difference between precept and already claimed amount including tax in a local currency. */
	public Number $differenceTaxInclusiveAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('DifferenceTaxInclusiveAmount');
		set {
			$this->node->setNumber('DifferenceTaxInclusiveAmount', $value);
		}
	}

	/** Difference between precept and already claimed amount including tax in a foreign currency. */
	public ?Number $differenceTaxInclusiveAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('DifferenceTaxInclusiveAmountCurr');
		set {
			$this->node->setNumber('DifferenceTaxInclusiveAmountCurr', $value);
		}
	}

	/** Rounding of the total amount in a local currency. */
	public ?Number $payableRoundingAmount {
		/** @throws Exception */
		get => $this->node->getNumber('PayableRoundingAmount');
		set {
			$this->node->setNumber('PayableRoundingAmount', $value);
		}
	}

	/** Rounding of the total amount in a foreign currency. */
	public ?Number $payableRoundingAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('PayableRoundingAmountCurr');
		set {
			$this->node->setNumber('PayableRoundingAmountCurr', $value);
		}
	}

	/** Paid non-taxable deposit in a local currency. */
	public Number $paidDepositsAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('PaidDepositsAmount');
		set {
			$this->node->setNumber('PaidDepositsAmount', $value);
		}
	}

	/** Paid non-taxable deposit in a foreign currency. */
	public ?Number $paidDepositsAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('PaidDepositsAmountCurr');
		set {
			$this->node->setNumber('PaidDepositsAmountCurr', $value);
		}
	}

	/** Payable amount in a local currency. */
	public Number $payableAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('PayableAmount');
		set {
			$this->node->setNumber('PayableAmount', $value);
		}
	}

	/** Payable amount in a foreign currency. */
	public ?Number $payableAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('PayableAmountCurr');
		set {
			$this->node->setNumber('PayableAmountCurr', $value);
		}
	}

	public function __construct(
		Number $taxExclusiveAmount,
		Number $taxInclusiveAmount,
		Number $alreadyClaimedTaxExclusiveAmount,
		Number $alreadyClaimedTaxInclusiveAmount,
		Number $differenceTaxExclusiveAmount,
		Number $differenceTaxInclusiveAmount,
		Number $paidDepositsAmount,
		Number $payableAmount,
	)
	{
		$this->taxExclusiveAmount = $taxExclusiveAmount;
		$this->taxInclusiveAmount = $taxInclusiveAmount;
		$this->alreadyClaimedTaxExclusiveAmount = $alreadyClaimedTaxExclusiveAmount;
		$this->alreadyClaimedTaxInclusiveAmount = $alreadyClaimedTaxInclusiveAmount;
		$this->differenceTaxExclusiveAmount = $differenceTaxExclusiveAmount;
		$this->differenceTaxInclusiveAmount = $differenceTaxInclusiveAmount;
		$this->paidDepositsAmount = $paidDepositsAmount;
		$this->payableAmount = $payableAmount;
	}

}