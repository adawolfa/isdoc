<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about a contactable person or organization department.
 *
 * @property string|null $name
 * @property string|null $telephone
 * @property string|null $electronicMail
 */
class Contact implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Contact name. */
	#[Map('Name')]
	private ?string $name = null;

	/** Phone number. */
	#[Map('Telephone')]
	private ?string $telephone = null;

	/** E-mail address. */
	#[Map('ElectronicMail')]
	private ?string $electronicMail = null;

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

	/** @deprecated Method accessors are deprecated, use {@see $telephone} property instead. */
	public function getTelephone(): ?string
	{
		return $this->telephone;
	}

	/** @deprecated Method accessors are deprecated, use {@see $telephone} property instead. */
	public function setTelephone(?string $telephone): self
	{
		$this->telephone = $telephone;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $electronicMail} property instead. */
	public function getElectronicMail(): ?string
	{
		return $this->electronicMail;
	}

	/** @deprecated Method accessors are deprecated, use {@see $electronicMail} property instead. */
	public function setElectronicMail(?string $electronicMail): self
	{
		$this->electronicMail = $electronicMail;
		return $this;
	}

}