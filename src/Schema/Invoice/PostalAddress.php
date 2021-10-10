<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Postal address.
 *
 * @property string  $streetName
 * @property string  $buildingNumber
 * @property string  $cityName
 * @property string  $postalZone
 * @property Country $country
 */
class PostalAddress implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Street.
	 *
	 * @Map("StreetName")
	 */
	private string $streetName;

	/**
	 * Building number.
	 *
	 * @Map("BuildingNumber")
	 */
	private string $buildingNumber;

	/**
	 * City.
	 *
	 * @Map("CityName")
	 */
	private string $cityName;

	/**
	 * ZIP/postal zone.
	 *
	 * @Map("PostalZone")
	 */
	private string $postalZone;

	/**
	 * Country.
	 *
	 * @Map("Country")
	 */
	private Country $country;

	public function __construct(
		string $streetName,
		string $buildingNumber,
		string $cityName,
		string $postalZone,
		Country $country
	) {
		$this->setStreetName($streetName);
		$this->setBuildingNumber($buildingNumber);
		$this->setCityName($cityName);
		$this->setPostalZone($postalZone);
		$this->setCountry($country);
	}

	public function getStreetName(): string
	{
		return $this->streetName;
	}

	public function setStreetName(string $streetName): self
	{
		$this->streetName = $streetName;
		return $this;
	}

	public function getBuildingNumber(): string
	{
		return $this->buildingNumber;
	}

	public function setBuildingNumber(string $buildingNumber): self
	{
		$this->buildingNumber = $buildingNumber;
		return $this;
	}

	public function getCityName(): string
	{
		return $this->cityName;
	}

	public function setCityName(string $cityName): self
	{
		$this->cityName = $cityName;
		return $this;
	}

	public function getPostalZone(): string
	{
		return $this->postalZone;
	}

	public function setPostalZone(string $postalZone): self
	{
		$this->postalZone = $postalZone;
		return $this;
	}

	public function getCountry(): Country
	{
		return $this->country;
	}

	public function setCountry(Country $country): self
	{
		$this->country = $country;
		return $this;
	}

}