<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Reference;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Reference to line on a related purchase order.
 *
 * @property Order       $order
 * @property string|null $lineID
 */
class OrderLine implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** @Reference */
	private Order $order;

	/**
	 * Line number.
	 *
	 * @Map("LineID")
	 */
	private ?string $lineID = null;

	public function __construct(Order $order)
	{
		$this->setOrder($order);
	}

	public function getOrder(): Order
	{
		return $this->order;
	}

	public function setOrder(Order $order): self
	{
		$this->order = $order;
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