<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use DateTimeImmutable;
use Nette\SmartObject;

/**
 * Commercial Register record identification (in the Czech Republic).
 *
 * @property string|null            $registerKeptAt
 * @property string|null            $registerFileRef
 * @property DateTimeImmutable|null $registerDate
 * @property string|null            $preformatted
 */
class RegisterIdentification implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Commercial Register administrator.
	 *
	 * @Map("RegisterKeptAt")
	 */
	private ?string $registerKeptAt = null;

	/**
	 * Commercial Register number.
	 *
	 * @Map("RegisterFileRef")
	 */
	private ?string $registerFileRef = null;

	/**
	 * Registration date.
	 *
	 * @Map("RegisterDate")
	 */
	private ?DateTimeImmutable $registerDate = null;

	/**
	 * Preformatted information about registration in the Commerical Register.
	 *
	 * @Map("Preformatted")
	 */
	private ?string $preformatted = null;

	public function getRegisterKeptAt(): ?string
	{
		return $this->registerKeptAt;
	}

	public function setRegisterKeptAt(?string $registerKeptAt): self
	{
		$this->registerKeptAt = $registerKeptAt;
		return $this;
	}

	public function getRegisterFileRef(): ?string
	{
		return $this->registerFileRef;
	}

	public function setRegisterFileRef(?string $registerFileRef): self
	{
		$this->registerFileRef = $registerFileRef;
		return $this;
	}

	public function getRegisterDate(): ?DateTimeImmutable
	{
		return $this->registerDate;
	}

	public function setRegisterDate(?DateTimeImmutable $registerDate): self
	{
		$this->registerDate = $registerDate;
		return $this;
	}

	public function getPreformatted(): ?string
	{
		return $this->preformatted;
	}

	public function setPreformatted(?string $preformatted): self
	{
		$this->preformatted = $preformatted;
		return $this;
	}

}