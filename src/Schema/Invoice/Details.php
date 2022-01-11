<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use DateTimeImmutable;
use Nette\SmartObject;

/**
 * Payment details.
 *
 * @property string|null            $documentID
 * @property DateTimeImmutable|null $issueDate
 * @property DateTimeImmutable|null $paymentDueDate
 * @property string|null            $id
 * @property string|null            $bankCode
 * @property string|null            $name
 * @property string|null            $iban
 * @property string|null            $bic
 * @property string|null            $variableSymbol
 * @property string|null            $constantSymbol
 * @property string|null            $specificSymbol
 */
class Details implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Identifier of paired document, for example of bill. */
	#[Map('DocumentID')]
	private ?string $documentID = null;

	/** Issue date. */
	#[Map('IssueDate')]
	private ?DateTimeImmutable $issueDate = null;

	/** Due date. */
	#[Map('PaymentDueDate')]
	private ?DateTimeImmutable $paymentDueDate = null;

	/** Account number. */
	#[Map('ID')]
	private ?string $id = null;

	/** Bank code. */
	#[Map('BankCode')]
	private ?string $bankCode = null;

	/** A character string that constitutes the distinctive designation of a person, place, thing or concept. */
	#[Map('Name')]
	private ?string $name = null;

	/** International bank account number (IBAN). */
	#[Map('IBAN')]
	private ?string $iban = null;

	/** Bank identifier code as defined in ISO 9362. */
	#[Map('BIC')]
	private ?string $bic = null;

	/** Variable symbol (distinctive symbol of payment, typically number of invoice). Used for payment inside of the Czech Republic. */
	#[Map('VariableSymbol')]
	private ?string $variableSymbol = null;

	/** Constant symbol (used for payment inside of the Czech Republic). */
	#[Map('ConstantSymbol')]
	private ?string $constantSymbol = null;

	/** Specific symbol (used for payment inside of the Czech Republic). */
	#[Map('SpecificSymbol')]
	private ?string $specificSymbol = null;

	public function getDocumentID(): ?string
	{
		return $this->documentID;
	}

	public function setDocumentID(?string $documentID): self
	{
		$this->documentID = $documentID;
		return $this;
	}

	public function getIssueDate(): ?DateTimeImmutable
	{
		return $this->issueDate;
	}

	public function setIssueDate(?DateTimeImmutable $issueDate): self
	{
		$this->issueDate = $issueDate;
		return $this;
	}

	public function getPaymentDueDate(): ?DateTimeImmutable
	{
		return $this->paymentDueDate;
	}

	public function setPaymentDueDate(?DateTimeImmutable $paymentDueDate): self
	{
		$this->paymentDueDate = $paymentDueDate;
		return $this;
	}

	public function getId(): ?string
	{
		return $this->id;
	}

	public function setId(?string $id): self
	{
		$this->id = $id;
		return $this;
	}

	public function getBankCode(): ?string
	{
		return $this->bankCode;
	}

	public function setBankCode(?string $bankCode): self
	{
		$this->bankCode = $bankCode;
		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(?string $name): self
	{
		$this->name = $name;
		return $this;
	}

	public function getIban(): ?string
	{
		return $this->iban;
	}

	public function setIban(?string $iban): self
	{
		$this->iban = $iban;
		return $this;
	}

	public function getBic(): ?string
	{
		return $this->bic;
	}

	public function setBic(?string $bic): self
	{
		$this->bic = $bic;
		return $this;
	}

	public function getVariableSymbol(): ?string
	{
		return $this->variableSymbol;
	}

	public function setVariableSymbol(?string $variableSymbol): self
	{
		$this->variableSymbol = $variableSymbol;
		return $this;
	}

	public function getConstantSymbol(): ?string
	{
		return $this->constantSymbol;
	}

	public function setConstantSymbol(?string $constantSymbol): self
	{
		$this->constantSymbol = $constantSymbol;
		return $this;
	}

	public function getSpecificSymbol(): ?string
	{
		return $this->specificSymbol;
	}

	public function setSpecificSymbol(?string $specificSymbol): self
	{
		$this->specificSymbol = $specificSymbol;
		return $this;
	}

}