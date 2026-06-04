<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Reference to line on a related purchase order.
 */
class OrderLine implements Entity
{

	use Backing;

	private ?Order $isdocReferenceOrder = null;

	public Order $order {
		/** @throws Exception */
		get {
			if ($this->isdocReferenceOrder !== null) {
				return $this->isdocReferenceOrder;
			}

			$ref = $this->node->getString('@ref');

			return $ref !== null
				? $this->node->getReference(Order::class, $ref)
				: $this->node->view(Order::class);
		}
		set {
			$this->isdocReferenceOrder = $value;
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
		Order $order,
	)
	{
		$this->order = $order;
	}

}