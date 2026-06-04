<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC;
use BcMath;

/**
 * Legal monetary total with auto-computed properties.
 */
class LegalMonetaryTotal extends ISDOC\Schema\Invoice\LegalMonetaryTotal
{

	private ISDOC\Schema\Invoice $invoice;

	private bool $taxExclusiveAmountAssigned;

	private bool $taxInclusiveAmountAssigned;

	public function __construct(ISDOC\Schema\Invoice $invoice)
	{
		parent::__construct('0', '0', '0', '0', '0', '0', '0', '0');
		$this->invoice                    = $invoice;
		$this->taxExclusiveAmountAssigned = false;
		$this->taxInclusiveAmountAssigned = false;
	}

	/**
	 * @param callable(ISDOC\Schema\Invoice\InvoiceLine): numeric-string $fn
	 */
	private function sum(callable $fn): string
	{
		$sum = '0';
		$scale = 0;

		foreach ($this->invoice->invoiceLines as $line) {

			$lineValue = $fn($line);

			$r = strrchr($lineValue, '.');
			if ($r !== false) {
				$scale = max($scale, strlen(substr($r, 1)));
			}

			$sum = bcadd($sum, $lineValue, $scale);

		}

		return $sum;
	}

	/** {@inheritDoc} */
	public function getTaxExclusiveAmount(): string
	{
		if ($this->taxExclusiveAmountAssigned) {
			return parent::getTaxExclusiveAmount();
		}

		return $this->sum(fn(ISDOC\Schema\Invoice\InvoiceLine $line): string => $line->getLineExtensionAmount());
	}

	/** {@inheritDoc} */
	public function setTaxExclusiveAmount(string|BcMath\Number $taxExclusiveAmount): self
	{
		$this->taxExclusiveAmountAssigned = true;
		parent::setTaxExclusiveAmount($taxExclusiveAmount);
		return $this;
	}

	/** {@inheritDoc} */
	public function getTaxInclusiveAmount(): string
	{
		if ($this->taxInclusiveAmountAssigned) {
			return parent::getTaxInclusiveAmount();
		}

		return $this->sum(fn(ISDOC\Schema\Invoice\InvoiceLine $line): string => $line->getLineExtensionAmountTaxInclusive());
	}

	/** {@inheritDoc} */
	public function setTaxInclusiveAmount(string|BcMath\Number $taxInclusiveAmount): self
	{
		$this->taxInclusiveAmountAssigned = true;
		parent::setTaxInclusiveAmount($taxInclusiveAmount);
		return $this;
	}

}