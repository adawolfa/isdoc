<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Anonymous receiver of simplified tax document.
 */
class AnonymousCustomerParty implements Entity
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

	/** Identification of schema used for identifier construction. */
	public ?string $idScheme {
		get => $this->node->getString('IDScheme');
		set {
			$this->node->setString('IDScheme', $value);
		}
	}

	public function __construct(
		string $id,
	)
	{
		$this->id = $id;
	}

}