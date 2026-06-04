<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory;
use BcMath\Number;

/**
 * Invoice line ({@code <InvoiceLine>}) that derives the tax-inclusive line total from the exclusive total and the
 * line tax, so callers pass one fewer redundant amount.
 *
 * The only rounding-free identity in a line is additive: the tax-inclusive total equals the tax-exclusive total
 * plus the line tax ({@code LineExtensionAmountTaxInclusive = LineExtensionAmount + LineExtensionTaxAmount}). This
 * decorator derives exactly that and nothing else — the unit prices and the VAT rate stay as the caller supplied
 * them, because turning a percentage into an amount needs a rounding policy this library deliberately does not own
 * (the same boundary {@see LegalMonetaryTotal} respects by only summing already-rounded line amounts).
 *
 * The total is derived once, at construction, from the two amounts passed in (both known up front, unlike the
 * sub-totals a {@see TaxTotal} sums later) — so the value is materialized immediately and needs no serialization-time
 * flush. Assigning {@see $lineExtensionAmount} or {@see $lineExtensionTaxAmount} afterwards does not recompute it.
 */
class InvoiceLine extends ISDOC\Schema\Invoice\InvoiceLine
{

	public function __construct(
		string                $id,
		Number                $lineExtensionAmount,
		Number                $lineExtensionTaxAmount,
		Number                $unitPrice,
		Number                $unitPriceTaxInclusive,
		ClassifiedTaxCategory $classifiedTaxCategory,
	)
	{
		parent::__construct(
			id:                              $id,
			lineExtensionAmount:             $lineExtensionAmount,
			lineExtensionAmountTaxInclusive: $lineExtensionAmount->add($lineExtensionTaxAmount),
			lineExtensionTaxAmount:          $lineExtensionTaxAmount,
			unitPrice:                       $unitPrice,
			unitPriceTaxInclusive:           $unitPriceTaxInclusive,
			classifiedTaxCategory:           $classifiedTaxCategory,
		);
	}

}