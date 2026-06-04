<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;

/**
 * Information about a contactable person or organization department.
 */
class Contact implements Entity
{

	use Backing;

	/** Contact name. */
	public ?string $name {
		get => $this->node->getString('Name');
		set {
			$this->node->setString('Name', $value);
		}
	}

	/** Phone number. */
	public ?string $telephone {
		get => $this->node->getString('Telephone');
		set {
			$this->node->setString('Telephone', $value);
		}
	}

	/** E-mail address. */
	public ?string $electronicMail {
		get => $this->node->getString('ElectronicMail');
		set {
			$this->node->setString('ElectronicMail', $value);
		}
	}

}