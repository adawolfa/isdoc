<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Information about a party's name.
 */
class PartyName implements Entity
{

	use Backing;

	/** A character string that constitutes the distinctive designation of a person, place, thing or concept. */
	public string $name {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('Name');
		set {
			$this->node->setString('Name', $value);
		}
	}

	public function __construct(
		string $name,
	)
	{
		$this->name = $name;
	}

}