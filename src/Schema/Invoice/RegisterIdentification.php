<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use DateTimeInterface;

/**
 * Commercial Register record identification (in the Czech Republic).
 */
class RegisterIdentification implements Entity
{

	use Backing;

	/** Commercial Register administrator. */
	public ?string $registerKeptAt {
		get => $this->node->getString('RegisterKeptAt');
		set {
			$this->node->setString('RegisterKeptAt', $value);
		}
	}

	/** Commercial Register number. */
	public ?string $registerFileRef {
		get => $this->node->getString('RegisterFileRef');
		set {
			$this->node->setString('RegisterFileRef', $value);
		}
	}

	/** Registration date. */
	public ?DateTimeInterface $registerDate {
		/** @throws Exception */
		get => $this->node->getDate('RegisterDate');
		set {
			$this->node->setDate('RegisterDate', $value);
		}
	}

	/** Preformatted information about registration in the Commerical Register. */
	public ?string $preformatted {
		get => $this->node->getString('Preformatted');
		set {
			$this->node->setString('Preformatted', $value);
		}
	}

}