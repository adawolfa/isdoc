<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use DateTimeInterface;
use Nette\SmartObject;

/**
 * Payment details.
 *
 * @property string|null            $documentID
 * @property DateTimeInterface|null $issueDate
 * @property DateTimeInterface|null $paymentDueDate
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
	private ?DateTimeInterface $issueDate = null;

	/** Due date. */
	#[Map('PaymentDueDate')]
	private ?DateTimeInterface $paymentDueDate = null;

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

	/** @deprecated Method accessors are deprecated, use {@see $documentID} property instead. */
	public function getDocumentID(): ?string
	{
		return $this->documentID;
	}

	/** @deprecated Method accessors are deprecated, use {@see $documentID} property instead. */
	public function setDocumentID(?string $documentID): self
	{
		$this->documentID = $documentID;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $issueDate} property instead. */
	public function getIssueDate(): ?DateTimeInterface
	{
		return $this->issueDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $issueDate} property instead. */
	public function setIssueDate(?DateTimeInterface $issueDate): self
	{
		$this->issueDate = $issueDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paymentDueDate} property instead. */
	public function getPaymentDueDate(): ?DateTimeInterface
	{
		return $this->paymentDueDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paymentDueDate} property instead. */
	public function setPaymentDueDate(?DateTimeInterface $paymentDueDate): self
	{
		$this->paymentDueDate = $paymentDueDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function getId(): ?string
	{
		return $this->id;
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function setId(?string $id): self
	{
		$this->id = $id;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $bankCode} property instead. */
	public function getBankCode(): ?string
	{
		return $this->bankCode;
	}

	/** @deprecated Method accessors are deprecated, use {@see $bankCode} property instead. */
	public function setBankCode(?string $bankCode): self
	{
		$this->bankCode = $bankCode;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $name} property instead. */
	public function getName(): ?string
	{
		return $this->name;
	}

	/** @deprecated Method accessors are deprecated, use {@see $name} property instead. */
	public function setName(?string $name): self
	{
		$this->name = $name;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $iban} property instead. */
	public function getIban(): ?string
	{
		return $this->iban;
	}

	/** @deprecated Method accessors are deprecated, use {@see $iban} property instead. */
	public function setIban(?string $iban): self
	{
		$this->iban = $iban;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $bic} property instead. */
	public function getBic(): ?string
	{
		return $this->bic;
	}

	/** @deprecated Method accessors are deprecated, use {@see $bic} property instead. */
	public function setBic(?string $bic): self
	{
		$this->bic = $bic;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $variableSymbol} property instead. */
	public function getVariableSymbol(): ?string
	{
		return $this->variableSymbol;
	}

	/** @deprecated Method accessors are deprecated, use {@see $variableSymbol} property instead. */
	public function setVariableSymbol(?string $variableSymbol): self
	{
		$this->variableSymbol = $variableSymbol;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $constantSymbol} property instead. */
	public function getConstantSymbol(): ?string
	{
		return $this->constantSymbol;
	}

	/** @deprecated Method accessors are deprecated, use {@see $constantSymbol} property instead. */
	public function setConstantSymbol(?string $constantSymbol): self
	{
		$this->constantSymbol = $constantSymbol;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $specificSymbol} property instead. */
	public function getSpecificSymbol(): ?string
	{
		return $this->specificSymbol;
	}

	/** @deprecated Method accessors are deprecated, use {@see $specificSymbol} property instead. */
	public function setSpecificSymbol(?string $specificSymbol): self
	{
		$this->specificSymbol = $specificSymbol;
		return $this;
	}

}