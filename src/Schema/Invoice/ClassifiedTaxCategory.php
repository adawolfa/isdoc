<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;

/**
 * Compound VAT field.
 */
class ClassifiedTaxCategory implements Entity
{

	use Backing;

	/** Percentage VAT rate. */
	public Number $percent {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('Percent');
		set {
			$this->node->setNumber('Percent', $value);
		}
	}

	/** VAT calculation method (there are two types in the Czech Republic). */
	public VATCalculationMethod $vatCalculationMethod {
		/** @throws Exception */
		get => $this->node->getEnumOrThrow('VATCalculationMethod', VATCalculationMethod::class);
		set { $this->node->setEnum('VATCalculationMethod', $value); }
	}

	/** VAT is applicable. */
	public ?bool $vatApplicable {
		/** @throws Exception */
		get => $this->node->getBool('VATApplicable');
		set {
			$this->node->setBool('VATApplicable', $value);
		}
	}

	/** Local reverse charge mode. */
	public ?LocalReverseCharge $localReverseCharge {
		get => $this->node->getChild('LocalReverseCharge', LocalReverseCharge::class);
		set { $this->node->setChild('LocalReverseCharge', $value); }
	}

	public function __construct(
		Number $percent,
		VATCalculationMethod $vatCalculationMethod,
	)
	{
		$this->percent = $percent;
		$this->vatCalculationMethod = $vatCalculationMethod;
	}

}