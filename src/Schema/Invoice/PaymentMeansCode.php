<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * The set of valid means of paying the debt incurred.
 */
enum PaymentMeansCode: int
{

	/** Cash payment. */
	case CashPayment = 10;

	/** Cheque payment. */
	case ChequePayment = 20;

	/** Credit transfer. */
	case CreditTransfer = 31;

	/** Money transfer to an account. */
	case MoneyTransferToAnAccount = 42;

	/** Card payment. */
	case CardPayment = 48;

	/** Direct debit. */
	case DirectDebit = 49;

	/** Payment by postgiro. */
	case PaymentByPostgiro = 50;

	/** Composition between partners. */
	case CompositionBetweenPartners = 97;

}