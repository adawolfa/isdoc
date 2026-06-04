<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Postal address.
 */
class PostalAddress implements Entity
{

	use Backing;

	/** Street. */
	public string $streetName {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('StreetName');
		set {
			$this->node->setString('StreetName', $value);
		}
	}

	/** Building number. */
	public string $buildingNumber {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('BuildingNumber');
		set {
			$this->node->setString('BuildingNumber', $value);
		}
	}

	/** City. */
	public string $cityName {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('CityName');
		set {
			$this->node->setString('CityName', $value);
		}
	}

	/** ZIP/postal zone. */
	public string $postalZone {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('PostalZone');
		set {
			$this->node->setString('PostalZone', $value);
		}
	}

	/** Country. */
	public Country $country {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('Country', Country::class);
		set { $this->node->setChild('Country', $value); }
	}

	public function __construct(
		string $streetName,
		string $buildingNumber,
		string $cityName,
		string $postalZone,
		Country $country,
	)
	{
		$this->streetName = $streetName;
		$this->buildingNumber = $buildingNumber;
		$this->cityName = $cityName;
		$this->postalZone = $postalZone;
		$this->country = $country;
	}

}