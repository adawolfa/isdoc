<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information directly relating to a specific payment.
 *
 * @property string       $paidAmount
 * @property int          $paymentMeansCode
 * @property Details|null $details
 * @property bool|null    $partialPayment
 */
class Payment implements Arrayable
{

	use SmartObject;
	use ToArray;

	public const PAYMENT_MEANS_CODE_CASH_PAYMENT                 = 10;
	public const PAYMENT_MEANS_CODE_CHEQUE_PAYMENT               = 20;
	public const PAYMENT_MEANS_CODE_CREDIT_TRANSFER              = 31;
	public const PAYMENT_MEANS_CODE_MONEY_TRANSFER_TO_AN_ACCOUNT = 42;
	public const PAYMENT_MEANS_CODE_CARD_PAYMENT                 = 48;
	public const PAYMENT_MEANS_CODE_DIRECT_DEBIT                 = 49;
	public const PAYMENT_MEANS_CODE_PAYMENT_BY_POSTGIRO          = 50;
	public const PAYMENT_MEANS_CODE_COMPOSITION_BETWEEN_PARTNERS = 97;

	/**
	 * Amount to be paid.
	 *
	 * @Map("PaidAmount")
	 */
	private string $paidAmount;

	/**
	 * The set of valid means of paying the debt incurred.
	 *
	 * @Map("PaymentMeansCode")
	 */
	private int $paymentMeansCode;

	/**
	 * Payment details.
	 *
	 * @Map("Details")
	 */
	private ?Details $details = null;

	/**
	 * Flag indicating that partial payment is permitted.
	 *
	 * @Map("@partialPayment")
	 */
	private ?bool $partialPayment = null;

	public function __construct(string $paidAmount, int $paymentMeansCode)
	{
		$this->setPaidAmount($paidAmount);
		$this->setPaymentMeansCode($paymentMeansCode);
	}

	public function getPaidAmount(): string
	{
		return $this->paidAmount;
	}

	public function setPaidAmount(string $paidAmount): self
	{
		Restriction::decimal($paidAmount);
		$this->paidAmount = $paidAmount;
		return $this;
	}

	public function getPaymentMeansCode(): int
	{
		return $this->paymentMeansCode;
	}

	public function setPaymentMeansCode(int $paymentMeansCode): self
	{
		Restriction::enumeration($paymentMeansCode, [
			self::PAYMENT_MEANS_CODE_CASH_PAYMENT,
			self::PAYMENT_MEANS_CODE_CHEQUE_PAYMENT,
			self::PAYMENT_MEANS_CODE_CREDIT_TRANSFER,
			self::PAYMENT_MEANS_CODE_MONEY_TRANSFER_TO_AN_ACCOUNT,
			self::PAYMENT_MEANS_CODE_CARD_PAYMENT,
			self::PAYMENT_MEANS_CODE_DIRECT_DEBIT,
			self::PAYMENT_MEANS_CODE_PAYMENT_BY_POSTGIRO,
			self::PAYMENT_MEANS_CODE_COMPOSITION_BETWEEN_PARTNERS,
		]);
		$this->paymentMeansCode = $paymentMeansCode;
		return $this;
	}

	public function getDetails(): ?Details
	{
		return $this->details;
	}

	public function setDetails(?Details $details): self
	{
		$this->details = $details;
		return $this;
	}

	public function getPartialPayment(): ?bool
	{
		return $this->partialPayment;
	}

	public function setPartialPayment(?bool $partialPayment): self
	{
		$this->partialPayment = $partialPayment;
		return $this;
	}

}