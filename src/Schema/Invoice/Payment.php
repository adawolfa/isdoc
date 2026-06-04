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
 * Information directly relating to a specific payment.
 *
 * @property string|BcMath\Number $paidAmount
 * @property int                  $paymentMeansCode
 * @property Details|null         $details
 * @property bool|null            $partialPayment
 */
class Payment implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** @deprecated use {@see PaymentMeansCode::CashPayment} instead */
	public const int PAYMENT_MEANS_CODE_CASH_PAYMENT = PaymentMeansCode::CashPayment;

	/** @deprecated use {@see PaymentMeansCode::ChequePayment} instead */
	public const int PAYMENT_MEANS_CODE_CHEQUE_PAYMENT = PaymentMeansCode::ChequePayment;

	/** @deprecated use {@see PaymentMeansCode::CreditTransfer} instead */
	public const int PAYMENT_MEANS_CODE_CREDIT_TRANSFER = PaymentMeansCode::CreditTransfer;

	/** @deprecated use {@see PaymentMeansCode::MoneyTransferToAnAccount} instead */
	public const int PAYMENT_MEANS_CODE_MONEY_TRANSFER_TO_AN_ACCOUNT = PaymentMeansCode::MoneyTransferToAnAccount;

	/** @deprecated use {@see PaymentMeansCode::CardPayment} instead */
	public const int PAYMENT_MEANS_CODE_CARD_PAYMENT = PaymentMeansCode::CardPayment;

	/** @deprecated use {@see PaymentMeansCode::DirectDebit} instead */
	public const int PAYMENT_MEANS_CODE_DIRECT_DEBIT = PaymentMeansCode::DirectDebit;

	/** @deprecated use {@see PaymentMeansCode::PaymentByPostgiro} instead */
	public const int PAYMENT_MEANS_CODE_PAYMENT_BY_POSTGIRO = PaymentMeansCode::PaymentByPostgiro;

	/** @deprecated use {@see PaymentMeansCode::CompositionBetweenPartners} instead */
	public const int PAYMENT_MEANS_CODE_COMPOSITION_BETWEEN_PARTNERS = PaymentMeansCode::CompositionBetweenPartners;

	/** Amount to be paid. */
	#[Map('PaidAmount')]
	private string $paidAmount;

	/** The set of valid means of paying the debt incurred. */
	#[Map('PaymentMeansCode')]
	private int $paymentMeansCode;

	/** Payment details. */
	#[Map('Details')]
	private ?Details $details = null;

	/** Flag indicating that partial payment is permitted. */
	#[Map('@partialPayment')]
	private ?bool $partialPayment = null;

	public function __construct(string|BcMath\Number $paidAmount, int $paymentMeansCode)
	{
		$this->setPaidAmount($paidAmount);
		$this->setPaymentMeansCode($paymentMeansCode);
	}

	/** @deprecated Method accessors are deprecated, use {@see $paidAmount} property instead. */
	public function getPaidAmount(): string
	{
		return $this->paidAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paidAmount} property instead. */
	public function setPaidAmount(string|BcMath\Number $paidAmount): self
	{
		Deprecations::number($paidAmount);
		$paidAmount = (string) $paidAmount;
		Restriction::decimal($paidAmount);
		$this->paidAmount = $paidAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paymentMeansCode} property instead. */
	public function getPaymentMeansCode(): int
	{
		return $this->paymentMeansCode;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paymentMeansCode} property instead. */
	public function setPaymentMeansCode(int $paymentMeansCode): self
	{
		Restriction::enumeration($paymentMeansCode, [
			PaymentMeansCode::CashPayment,
			PaymentMeansCode::ChequePayment,
			PaymentMeansCode::CreditTransfer,
			PaymentMeansCode::MoneyTransferToAnAccount,
			PaymentMeansCode::CardPayment,
			PaymentMeansCode::DirectDebit,
			PaymentMeansCode::PaymentByPostgiro,
			PaymentMeansCode::CompositionBetweenPartners,
		]);
		$this->paymentMeansCode = $paymentMeansCode;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $details} property instead. */
	public function getDetails(): ?Details
	{
		return $this->details;
	}

	/** @deprecated Method accessors are deprecated, use {@see $details} property instead. */
	public function setDetails(?Details $details): self
	{
		$this->details = $details;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $partialPayment} property instead. */
	public function getPartialPayment(): ?bool
	{
		return $this->partialPayment;
	}

	/** @deprecated Method accessors are deprecated, use {@see $partialPayment} property instead. */
	public function setPartialPayment(?bool $partialPayment): self
	{
		$this->partialPayment = $partialPayment;
		return $this;
	}

}