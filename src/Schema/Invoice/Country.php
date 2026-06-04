<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Country.
 *
 * @property string $identificationCode
 * @property string $name
 */
class Country implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** ISO 3166 country code. */
	#[Map('IdentificationCode')]
	private string $identificationCode;

	/** Country name. */
	#[Map('Name')]
	private string $name;

	public function __construct(string $identificationCode, string $name)
	{
		$this->setIdentificationCode($identificationCode);
		$this->setName($name);
	}

	/** @deprecated Method accessors are deprecated, use {@see $identificationCode} property instead. */
	public function getIdentificationCode(): string
	{
		return $this->identificationCode;
	}

	/** @deprecated Method accessors are deprecated, use {@see $identificationCode} property instead. */
	public function setIdentificationCode(string $identificationCode): self
	{
		$this->identificationCode = $identificationCode;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $name} property instead. */
	public function getName(): string
	{
		return $this->name;
	}

	/** @deprecated Method accessors are deprecated, use {@see $name} property instead. */
	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

}