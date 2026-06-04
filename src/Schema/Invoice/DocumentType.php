<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * Document type.
 */
enum DocumentType: int
{

	/** invoice. */
	case Invoice = 1;

	/** credit note. */
	case CreditNote = 2;

	/** debit note. */
	case DebitNote = 3;

	/** proforma invoice (no VAT). */
	case ProformaInvoiceNoVAT = 4;

	/** advance invoice (with VAT). */
	case AdvanceInvoiceWithVAT = 5;

	/** credit note for advance invoice (with VAT). */
	case CreditNoteForAdvanceInvoiceWithVAT = 6;

	/** simplified tax document. */
	case SimplifiedTaxDocument = 7;

}