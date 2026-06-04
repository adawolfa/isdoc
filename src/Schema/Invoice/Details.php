<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use DateTimeInterface;

/**
 * Payment details.
 */
class Details implements Entity
{

	use Backing;

	/** Identifier of paired document, for example of bill. */
	public ?string $documentID {
		get => $this->node->getString('DocumentID');
		set {
			$this->node->setString('DocumentID', $value);
		}
	}

	/** Issue date. */
	public ?DateTimeInterface $issueDate {
		/** @throws Exception */
		get => $this->node->getDate('IssueDate');
		set {
			$this->node->setDate('IssueDate', $value);
		}
	}

	/** Due date. */
	public ?DateTimeInterface $paymentDueDate {
		/** @throws Exception */
		get => $this->node->getDate('PaymentDueDate');
		set {
			$this->node->setDate('PaymentDueDate', $value);
		}
	}

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

	/** Variable symbol (distinctive symbol of payment, typically number of invoice). Used for payment inside of the Czech Republic. */
	public ?string $variableSymbol {
		get => $this->node->getString('VariableSymbol');
		set {
			$this->node->setString('VariableSymbol', $value);
		}
	}

	/** Constant symbol (used for payment inside of the Czech Republic). */
	public ?string $constantSymbol {
		get => $this->node->getString('ConstantSymbol');
		set {
			$this->node->setString('ConstantSymbol', $value);
		}
	}

	/** Specific symbol (used for payment inside of the Czech Republic). */
	public ?string $specificSymbol {
		get => $this->node->getString('SpecificSymbol');
		set {
			$this->node->setString('SpecificSymbol', $value);
		}
	}

}