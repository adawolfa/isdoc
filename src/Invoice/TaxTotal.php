<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC;
use BcMath\Number;

/**
 * Tax recapitulation ({@code <TaxTotal>}, "Daňová rekapitulace") whose total tax amount defaults to the bcmath
 * sum of its per-rate sub-totals ({@code <TaxSubTotal>}/{@code <TaxAmount>}) unless it was set explicitly.
 *
 * Mirrors {@see LegalMonetaryTotal}: the amount is read straight from the backing element when present (an explicit
 * value) and computed from the sub-totals otherwise. {@see flush()} writes the effective value into the document at
 * serialization time so the computed total appears in the output. The sub-totals are added after construction, so
 * the value cannot be materialized eagerly — hence the lazy fall-back plus the serialization-time flush.
 */
class TaxTotal extends ISDOC\Schema\Invoice\TaxTotal
{

	public Number $taxAmount {
		/** @throws ISDOC\XML\Exception */
		get => $this->node->getNumber('TaxAmount') ?? $this->sum();
		set {
			$this->node->setNumber('TaxAmount', $value);
		}
	}

	public function __construct()
	{
		parent::__construct(taxAmount: new Number('0'));

		// The total tax amount defaults to the sub-total sum and is materialized by flush() just before
		// serialization; drop the zero placeholder the parent constructor wrote so the get-hook falls back to the
		// computed sum instead of reading a stored zero.
		$this->node->remove('TaxAmount');
	}

	/**
	 * Materializes the effective total tax amount into the document (called before serialization).
	 *
	 * @throws ISDOC\XML\Exception
	 */
	public function flush(): void
	{
		$this->taxAmount = $this->taxAmount;
	}

	/**
	 * Sums the per-rate sub-total tax amounts ({@code <TaxSubTotal>}/{@code <TaxAmount>}). {@see Number} addition
	 * widens to the larger scale of its operands, so the result keeps as many decimal places as the most precise
	 * sub-total — matching the {@see LegalMonetaryTotal} line sum.
	 *
	 * @throws ISDOC\XML\Exception
	 */
	private function sum(): Number
	{
		$sum = new Number('0');

		foreach ($this as $subTotal) {
			$sum = $sum->add($subTotal->taxAmount);
		}

		return $sum;
	}

}