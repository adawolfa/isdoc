<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Buyer's item identification.
 */
class BuyersItemIdentification implements Entity
{

	use Backing;

	/** Unique identifier. */
	public string $id {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('ID');
		set {
			$this->node->setString('ID', $value);
		}
	}

	public function __construct(
		string $id,
	)
	{
		$this->id = $id;
	}

}