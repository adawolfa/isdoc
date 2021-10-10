<?php

declare(strict_types=1);
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

	/**
	 * ISO 3166 country code.
	 *
	 * @Map("IdentificationCode")
	 */
	private string $identificationCode;

	/**
	 * Country name.
	 *
	 * @Map("Name")
	 */
	private string $name;

	public function __construct(string $identificationCode, string $name)
	{
		$this->setIdentificationCode($identificationCode);
		$this->setName($name);
	}

	public function getIdentificationCode(): string
	{
		return $this->identificationCode;
	}

	public function setIdentificationCode(string $identificationCode): self
	{
		$this->identificationCode = $identificationCode;
		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

}