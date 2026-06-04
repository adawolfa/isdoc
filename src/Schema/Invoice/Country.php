<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Country.
 */
class Country implements Entity
{

	use Backing;

	/** ISO 3166 country code. */
	public string $identificationCode {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('IdentificationCode');
		set {
			$this->node->setString('IdentificationCode', $value);
		}
	}

	/** Country name. */
	public string $name {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('Name');
		set {
			$this->node->setString('Name', $value);
		}
	}

	public function __construct(
		string $identificationCode,
		string $name,
	)
	{
		$this->identificationCode = $identificationCode;
		$this->name = $name;
	}

}