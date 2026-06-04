<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * Document type.
 *
 * Forward-compatible counterpart of the DocumentType enum introduced in 2.0. Reference these constants
 * instead of the deprecated Invoice::DOCUMENT_TYPE_* constants to keep the upgrade seamless.
 */
final class DocumentType
{

	public const int Invoice = 1;
	public const int CreditNote = 2;
	public const int DebitNote = 3;
	public const int ProformaInvoiceNoVAT = 4;
	public const int AdvanceInvoiceWithVAT = 5;
	public const int CreditNoteForAdvanceInvoiceWithVAT = 6;
	public const int SimplifiedTaxDocument = 7;

	private function __construct()
	{
	}

}
