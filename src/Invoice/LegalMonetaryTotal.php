<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice\InvoiceLine;
use BcMath\Number;

/**
 * Legal monetary total whose tax-exclusive and tax-inclusive amounts default to the bcmath sum of the invoice
 * lines unless they were set explicitly.
 *
 * The two amounts are read straight from the backing element when present (an explicit value) and computed from
 * the lines otherwise. {@see flush()} writes the effective values into the document at serialization time so the
 * computed totals appear in the output; the remaining required totals default to zero.
 */
class LegalMonetaryTotal extends ISDOC\Schema\Invoice\LegalMonetaryTotal
{

	private ISDOC\Schema\Invoice $invoice;

	public Number $taxExclusiveAmount {
		/** @throws ISDOC\XML\Exception */
		get => $this->node->getNumber('TaxExclusiveAmount')
			?? $this->sum(static fn(InvoiceLine $line): Number => $line->lineExtensionAmount);
		set {
			$this->node->setNumber('TaxExclusiveAmount', $value);
		}
	}

	public Number $taxInclusiveAmount {
		/** @throws ISDOC\XML\Exception */
		get => $this->node->getNumber('TaxInclusiveAmount')
			?? $this->sum(static fn(InvoiceLine $line): Number => $line->lineExtensionAmountTaxInclusive);
		set {
			$this->node->setNumber('TaxInclusiveAmount', $value);
		}
	}

	public function __construct(ISDOC\Schema\Invoice $invoice)
	{
		$zero = new Number('0');

		parent::__construct(
			taxExclusiveAmount:               $zero,
			taxInclusiveAmount:               $zero,
			alreadyClaimedTaxExclusiveAmount: $zero,
			alreadyClaimedTaxInclusiveAmount: $zero,
			differenceTaxExclusiveAmount:     $zero,
			differenceTaxInclusiveAmount:     $zero,
			paidDepositsAmount:               $zero,
			payableAmount:                    $zero,
		);

		$this->invoice = $invoice;

		// The tax-exclusive/inclusive totals default to the invoice-line sums and are materialized by flush()
		// just before serialization; drop the zero placeholders the parent constructor wrote so the get-hooks
		// fall back to the computed sums instead of reading a stored zero.
		$this->node->remove('TaxExclusiveAmount');
		$this->node->remove('TaxInclusiveAmount');
	}

	/**
	 * Materializes the effective tax-exclusive/inclusive totals into the document (called before serialization).
	 *
	 * @throws ISDOC\XML\Exception
	 */
	public function flush(): void
	{
		$exclusive = $this->taxExclusiveAmount;
		$inclusive = $this->taxInclusiveAmount;

		$this->taxExclusiveAmount = $exclusive;
		$this->taxInclusiveAmount = $inclusive;
	}

	/**
	 * Sums a per-line amount across the invoice lines. {@see Number} addition widens to the larger scale of its
	 * operands, so the result keeps as many decimal places as the most precise line — matching the previous
	 * bcmath sum that grew its scale to the widest summand.
	 *
	 * @param callable(InvoiceLine): Number $amount
	 */
	private function sum(callable $amount): Number
	{
		$sum = new Number('0');

		foreach ($this->invoice->invoiceLines as $line) {
			$sum = $sum->add($amount($line));
		}

		return $sum;
	}

}