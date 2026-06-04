<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Local reverse charge mode.
 */
class LocalReverseCharge implements Entity
{

	use Backing;

	/** VAT subject code for local reverse charge mode. */
	public LocalReverseChargeCode $localReverseChargeCode {
		/** @throws Exception */
		get => $this->node->getEnumOrThrow('LocalReverseChargeCode', LocalReverseChargeCode::class);
		set { $this->node->setEnum('LocalReverseChargeCode', $value); }
	}

	/** Amount. */
	public ?Quantity $localReverseChargeQuantity {
		get => $this->node->getChild('LocalReverseChargeQuantity', Quantity::class);
		set { $this->node->setChild('LocalReverseChargeQuantity', $value); }
	}

	public function __construct(
		LocalReverseChargeCode $localReverseChargeCode,
	)
	{
		$this->localReverseChargeCode = $localReverseChargeCode;
	}

}