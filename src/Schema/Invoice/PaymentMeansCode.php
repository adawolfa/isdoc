<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * The set of valid means of paying the debt incurred.
 *
 * Forward-compatible counterpart of the PaymentMeansCode enum introduced in 2.0. Reference these constants
 * instead of the deprecated Payment::PAYMENT_MEANS_CODE_* constants to keep the upgrade seamless.
 */
final class PaymentMeansCode
{

	public const int CashPayment = 10;
	public const int ChequePayment = 20;
	public const int CreditTransfer = 31;
	public const int MoneyTransferToAnAccount = 42;
	public const int CardPayment = 48;
	public const int DirectDebit = 49;
	public const int PaymentByPostgiro = 50;
	public const int CompositionBetweenPartners = 97;

	private function __construct()
	{
	}

}
