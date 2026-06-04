<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Information about delivery.
 */
class Delivery implements Entity
{

	use Backing;

	/** Information about an organization, sub-organization, or individual fulfilling a role in a business process. */
	public Party $party {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('Party', Party::class);
		set { $this->node->setChild('Party', $value); }
	}

	public function __construct(
		Party $party,
	)
	{
		$this->party = $party;
	}

}