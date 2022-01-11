<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about a party's tax scheme.
 *
 * @property string $companyID
 * @property string $taxScheme
 */
class PartyTaxScheme implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** VAT number. */
	#[Map('CompanyID')]
	private string $companyID;

	/** Information about a tax scheme. The most common values are VAT (Value Added Tax) and TIN (Tax Identification Number). */
	#[Map('TaxScheme')]
	private string $taxScheme;

	public function __construct(string $companyID, string $taxScheme)
	{
		$this->setCompanyID($companyID);
		$this->setTaxScheme($taxScheme);
	}

	public function getCompanyID(): string
	{
		return $this->companyID;
	}

	public function setCompanyID(string $companyID): self
	{
		$this->companyID = $companyID;
		return $this;
	}

	public function getTaxScheme(): string
	{
		return $this->taxScheme;
	}

	public function setTaxScheme(string $taxScheme): self
	{
		$this->taxScheme = $taxScheme;
		return $this;
	}

}