<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about a bank account.
 *
 * @property string|null $id
 * @property string|null $bankCode
 * @property string|null $name
 * @property string|null $iban
 * @property string|null $bic
 */
class AlternateBankAccount implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Account number.
	 *
	 * @Map("ID")
	 */
	private ?string $id = null;

	/**
	 * Bank code.
	 *
	 * @Map("BankCode")
	 */
	private ?string $bankCode = null;

	/**
	 * A character string that constitutes the distinctive designation of a person, place, thing or concept.
	 *
	 * @Map("Name")
	 */
	private ?string $name = null;

	/**
	 * International bank account number (IBAN).
	 *
	 * @Map("IBAN")
	 */
	private ?string $iban = null;

	/**
	 * Bank identifier code as defined in ISO 9362.
	 *
	 * @Map("BIC")
	 */
	private ?string $bic = null;

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

}