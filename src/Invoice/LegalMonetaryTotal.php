<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC;

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

	private function sum(callable $fn): string
	{
		$sum = '0';
		$scale = 0;

		foreach ($this->invoice->invoiceLines as $line) {
			$lineValue = $fn($line);
			$scale = max($scale, strlen(substr(strrchr($lineValue, '.'), 1)));
			$sum = bcadd($sum, $lineValue, $scale);
		}

		return $sum;
	}

	public function getTaxExclusiveAmount(): string
	{
		if ($this->taxExclusiveAmountAssigned) {
			return parent::getTaxExclusiveAmount();
		}

		return $this->sum(fn(ISDOC\Schema\Invoice\InvoiceLine $line): string => $line->getLineExtensionAmount());
	}

	public function setTaxExclusiveAmount(string $taxExclusiveAmount): self
	{
		$this->taxExclusiveAmountAssigned = true;
		parent::setTaxExclusiveAmount($taxExclusiveAmount);
		return $this;
	}

	public function getTaxInclusiveAmount(): string
	{
		if ($this->taxInclusiveAmountAssigned) {
			return parent::getTaxInclusiveAmount();
		}

		return $this->sum(fn(ISDOC\Schema\Invoice\InvoiceLine $line): string => $line->getLineExtensionAmountTaxInclusive());
	}

	public function setTaxInclusiveAmount(string $taxInclusiveAmount): self
	{
		$this->taxInclusiveAmountAssigned = true;
		parent::setTaxInclusiveAmount($taxInclusiveAmount);
		return $this;
	}

}