<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;

/**
 * Information about a bank account.
 */
class AlternateBankAccount implements Entity
{

	use Backing;

	/** Account number. */
	public ?string $id {
		get => $this->node->getString('ID');
		set {
			$this->node->setString('ID', $value);
		}
	}

	/** Bank code. */
	public ?string $bankCode {
		get => $this->node->getString('BankCode');
		set {
			$this->node->setString('BankCode', $value);
		}
	}

	/** A character string that constitutes the distinctive designation of a person, place, thing or concept. */
	public ?string $name {
		get => $this->node->getString('Name');
		set {
			$this->node->setString('Name', $value);
		}
	}

	/** International bank account number (IBAN). */
	public ?string $iban {
		get => $this->node->getString('IBAN');
		set {
			$this->node->setString('IBAN', $value);
		}
	}

	/** Bank identifier code as defined in ISO 9362. */
	public ?string $bic {
		get => $this->node->getString('BIC');
		set {
			$this->node->setString('BIC', $value);
		}
	}

}