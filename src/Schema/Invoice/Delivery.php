<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about delivery.
 *
 * @property Party $party
 */
class Delivery implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Information about an organization, sub-organization, or individual fulfilling a role in a business process.
	 *
	 * @Map("Party")
	 */
	private Party $party;

	public function __construct(Party $party)
	{
		$this->setParty($party);
	}

	public function getParty(): Party
	{
		return $this->party;
	}

	public function setParty(Party $party): self
	{
		$this->party = $party;
		return $this;
	}

}