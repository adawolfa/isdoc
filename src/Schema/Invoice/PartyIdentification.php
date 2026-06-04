<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Information about a party's identification.
 */
class PartyIdentification implements Entity
{

	use Backing;

	/** User defined company/workplace number. */
	public ?string $userID {
		get => $this->node->getString('UserID');
		set {
			$this->node->setString('UserID', $value);
		}
	}

	/** International company/workplace number, e.g. EAN. */
	public ?string $catalogFirmIdentification {
		get => $this->node->getString('CatalogFirmIdentification');
		set {
			$this->node->setString('CatalogFirmIdentification', $value);
		}
	}

	/** Company identification number. */
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