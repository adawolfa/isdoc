<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;

/**
 * Information about a tax rate.
 */
class TaxCategory implements Entity
{

	use Backing;

	/** Tax rate expressed as a percentage. */
	public Number $percent {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('Percent');
		set {
			$this->node->setNumber('Percent', $value);
		}
	}

	/** Information about a tax scheme. The most common values are VAT (Value Added Tax) and TIN (Tax Identification Number). */
	public ?string $taxScheme {
		get => $this->node->getString('TaxScheme');
		set {
			$this->node->setString('TaxScheme', $value);
		}
	}

	/** VAT is applicable. */
	public ?bool $vatApplicable {
		/** @throws Exception */
		get => $this->node->getBool('VATApplicable');
		set {
			$this->node->setBool('VATApplicable', $value);
		}
	}

	/** Is tax rate included in a local reverse charge mode?. */
	public ?bool $localReverseChargeFlag {
		/** @throws Exception */
		get => $this->node->getBool('LocalReverseChargeFlag');
		set {
			$this->node->setBool('LocalReverseChargeFlag', $value);
		}
	}

	public function __construct(
		Number $percent,
	)
	{
		$this->percent = $percent;
	}

}