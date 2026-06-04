<?php declare(strict_types=1);

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

}