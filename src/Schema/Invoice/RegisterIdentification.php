<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use DateTimeInterface;
use Nette\SmartObject;

/**
 * Commercial Register record identification (in the Czech Republic).
 *
 * @property string|null            $registerKeptAt
 * @property string|null            $registerFileRef
 * @property DateTimeInterface|null $registerDate
 * @property string|null            $preformatted
 */
class RegisterIdentification implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Commercial Register administrator. */
	#[Map('RegisterKeptAt')]
	private ?string $registerKeptAt = null;

	/** Commercial Register number. */
	#[Map('RegisterFileRef')]
	private ?string $registerFileRef = null;

	/** Registration date. */
	#[Map('RegisterDate')]
	private ?DateTimeInterface $registerDate = null;

	/** Preformatted information about registration in the Commerical Register. */
	#[Map('Preformatted')]
	private ?string $preformatted = null;

	/** @deprecated Method accessors are deprecated, use {@see $registerKeptAt} property instead. */
	public function getRegisterKeptAt(): ?string
	{
		return $this->registerKeptAt;
	}

	/** @deprecated Method accessors are deprecated, use {@see $registerKeptAt} property instead. */
	public function setRegisterKeptAt(?string $registerKeptAt): self
	{
		$this->registerKeptAt = $registerKeptAt;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $registerFileRef} property instead. */
	public function getRegisterFileRef(): ?string
	{
		return $this->registerFileRef;
	}

	/** @deprecated Method accessors are deprecated, use {@see $registerFileRef} property instead. */
	public function setRegisterFileRef(?string $registerFileRef): self
	{
		$this->registerFileRef = $registerFileRef;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $registerDate} property instead. */
	public function getRegisterDate(): ?DateTimeInterface
	{
		return $this->registerDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $registerDate} property instead. */
	public function setRegisterDate(?DateTimeInterface $registerDate): self
	{
		$this->registerDate = $registerDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $preformatted} property instead. */
	public function getPreformatted(): ?string
	{
		return $this->preformatted;
	}

	/** @deprecated Method accessors are deprecated, use {@see $preformatted} property instead. */
	public function setPreformatted(?string $preformatted): self
	{
		$this->preformatted = $preformatted;
		return $this;
	}

}