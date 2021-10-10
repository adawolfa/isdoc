<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Buyer's item identification.
 *
 * @property string $id
 */
class BuyersItemIdentification implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Unique identifier.
	 *
	 * @Map("ID")
	 */
	private string $id;

	public function __construct(string $id)
	{
		$this->setId($id);
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

}