<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use DateTimeImmutable;

class Invoice extends Schema\Invoice
{

	public const VERSION = '6.0.1';

	public function __construct(
		string                                 $id,
		string                                 $uuid,
		DateTimeImmutable                      $issueDate,
		bool                                   $vatApplicable,
		string                                 $currencyCode,
		Schema\Invoice\AccountingSupplierParty $accountingSupplierParty
	)
	{
		parent::__construct(
			self::DOCUMENT_TYPE_INVOICE,
			$id,
			$uuid,
			$issueDate,
			$vatApplicable,
			new Schema\Invoice\Note,
			$currencyCode,
			'1.0',
			'1.0',
			$accountingSupplierParty,
			new Schema\Invoice\InvoiceLines,
			new Schema\Invoice\TaxTotal,
			new Invoice\LegalMonetaryTotal($this),
			self::VERSION,
		);
	}

}