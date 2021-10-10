<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Anonymous receiver of simplified tax document.
 *
 * @property string $id
 * @property  $idScheme
 */
class AnonymousCustomerParty implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Unique identifier.
	 *
	 * @Map("ID")
	 */
	private string $id;

	/**
	 * Identification of schema used for identifier construction.
	 *
	 * @Map("IDScheme")
	 */
	private $idScheme;

	public function __construct(string $id, $idScheme)
	{
		$this->setId($id);
		$this->setIdScheme($idScheme);
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

	public function getIdScheme()
	{
		return $this->idScheme;
	}

	public function setIdScheme($idScheme): self
	{
		$this->idScheme = $idScheme;
		return $this;
	}

}