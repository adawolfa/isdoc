<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Information about referenced line on delivery note.
 */
class DeliveryNoteLine implements Entity
{

	use Backing;

	private ?DeliveryNote $isdocReferenceDeliveryNote = null;

	public DeliveryNote $deliveryNote {
		/** @throws Exception */
		get {
			if ($this->isdocReferenceDeliveryNote !== null) {
				return $this->isdocReferenceDeliveryNote;
			}

			$ref = $this->node->getString('@ref');

			return $ref !== null
				? $this->node->getReference(DeliveryNote::class, $ref)
				: $this->node->view(DeliveryNote::class);
		}
		set {
			$this->isdocReferenceDeliveryNote = $value;
			$this->node->setReference($value);
		}
	}

	/** Line number. */
	public ?string $lineID {
		get => $this->node->getString('LineID');
		set {
			$this->node->setString('LineID', $value);
		}
	}

	public function __construct(
		DeliveryNote $deliveryNote,
	)
	{
		$this->deliveryNote = $deliveryNote;
	}

}