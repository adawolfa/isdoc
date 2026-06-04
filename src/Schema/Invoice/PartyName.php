<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about a party's name.
 *
 * @property string $name
 */
class PartyName implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** A character string that constitutes the distinctive designation of a person, place, thing or concept. */
	#[Map('Name')]
	private string $name;

	public function __construct(string $name)
	{
		$this->setName($name);
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