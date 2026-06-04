<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;

/**
 * Information directly relating to a specific payment.
 */
class Payment implements Entity
{

	use Backing;

	/** Amount to be paid. */
	public Number $paidAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('PaidAmount');
		set {
			$this->node->setNumber('PaidAmount', $value);
		}
	}

	/** The set of valid means of paying the debt incurred. */
	public PaymentMeansCode $paymentMeansCode {
		/** @throws Exception */
		get => $this->node->getEnumOrThrow('PaymentMeansCode', PaymentMeansCode::class);
		set { $this->node->setEnum('PaymentMeansCode', $value); }
	}

	/** Payment details. */
	public ?Details $details {
		get => $this->node->getChild('Details', Details::class);
		set { $this->node->setChild('Details', $value); }
	}

	/** Flag indicating that partial payment is permitted. */
	public ?bool $partialPayment {
		/** @throws Exception */
		get => $this->node->getBool('@partialPayment');
		set {
			$this->node->setBool('@partialPayment', $value);
		}
	}

	public function __construct(
		Number $paidAmount,
		PaymentMeansCode $paymentMeansCode,
	)
	{
		$this->paidAmount = $paidAmount;
		$this->paymentMeansCode = $paymentMeansCode;
	}

}