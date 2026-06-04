<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use DateTimeInterface;

/**
 * Decorated version of Invoice with more sane constructor.
 */
class Invoice extends Schema\Invoice
{

	/** @deprecated use {@see Invoice::Version} instead */
	public const string VERSION = self::Version;

	public const string Version = '6.0.2';

	public function __construct(
		string                                 $id,
		string                                 $uuid,
		DateTimeInterface                      $issueDate,
		bool                                   $vatApplicable,
		string                                 $currencyCode,
		Schema\Invoice\AccountingSupplierParty $accountingSupplierParty
	)
	{
		parent::__construct(
			Schema\Invoice\DocumentType::Invoice,
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
			new Schema\Invoice\TaxTotal('0.0'),
			new Invoice\LegalMonetaryTotal($this),
			self::Version,
		);
	}

}