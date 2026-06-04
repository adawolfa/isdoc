<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Information about a party's tax scheme.
 */
class PartyTaxScheme implements Entity
{

	use Backing;

	/** VAT number. */
	public string $companyID {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('CompanyID');
		set {
			$this->node->setString('CompanyID', $value);
		}
	}

	/** Information about a tax scheme. The most common values are VAT (Value Added Tax) and TIN (Tax Identification Number). */
	public string $taxScheme {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('TaxScheme');
		set {
			$this->node->setString('TaxScheme', $value);
		}
	}

	public function __construct(
		string $companyID,
		string $taxScheme,
	)
	{
		$this->companyID = $companyID;
		$this->taxScheme = $taxScheme;
	}

}