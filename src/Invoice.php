<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use BcMath\Number;
use DateTimeInterface;

/**
 * Decorated invoice with a saner constructor and auto-computed monetary totals.
 *
 * Implements {@see Finalizable} so the encoder flushes {@see Invoice\LegalMonetaryTotal}'s computed
 * tax-exclusive/inclusive sums into the backing document just before serialization.
 */
class Invoice extends Schema\Invoice implements Finalizable
{

	public const string Version = '6.0.2';

	public function __construct(
		string                                 $id,
		string                                 $uuid,
		DateTimeInterface                      $issueDate,
		bool                                   $vatApplicable,
		string                                 $currencyCode,
		Schema\Invoice\AccountingSupplierParty $accountingSupplierParty,
	)
	{
		parent::__construct(
			Schema\Invoice\DocumentType::Invoice,
			$id,
			$uuid,
			$issueDate,
			$vatApplicable,
			new Schema\Invoice\Note(),
			$currencyCode,
			new Number('1.0'),
			new Number('1.0'),
			$accountingSupplierParty,
			new Schema\Invoice\InvoiceLines(),
			new Invoice\TaxTotal(),
			new Invoice\LegalMonetaryTotal($this),
			self::Version,
		);
	}

	/** @throws XML\Exception */
	public function finalizeForWrite(): void
	{
		$taxTotal = $this->taxTotal;

		if ($taxTotal instanceof Invoice\TaxTotal) {
			$taxTotal->flush();
		}

		$legalMonetaryTotal = $this->legalMonetaryTotal;

		if ($legalMonetaryTotal instanceof Invoice\LegalMonetaryTotal) {
			$legalMonetaryTotal->flush();
		}
	}

}