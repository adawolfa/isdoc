<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Reference;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about referenced line on delivery note.
 *
 * @property DeliveryNote $deliveryNote
 * @property string|null  $lineID
 */
class DeliveryNoteLine implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** @Reference */
	private DeliveryNote $deliveryNote;

	/**
	 * Line number.
	 *
	 * @Map("LineID")
	 */
	private ?string $lineID = null;

	public function __construct(DeliveryNote $deliveryNote)
	{
		$this->setDeliveryNote($deliveryNote);
	}

	public function getDeliveryNote(): DeliveryNote
	{
		return $this->deliveryNote;
	}

	public function setDeliveryNote(DeliveryNote $deliveryNote): self
	{
		$this->deliveryNote = $deliveryNote;
		return $this;
	}

	public function getLineID(): ?string
	{
		return $this->lineID;
	}

	public function setLineID(?string $lineID): self
	{
		$this->lineID = $lineID;
		return $this;
	}

}