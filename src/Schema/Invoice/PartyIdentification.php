<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about a party's identification.
 *
 * @property string|null $userID
 * @property string|null $catalogFirmIdentification
 * @property string      $id
 */
class PartyIdentification implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** User defined company/workplace number. */
	#[Map('UserID')]
	private ?string $userID = null;

	/** International company/workplace number, e.g. EAN. */
	#[Map('CatalogFirmIdentification')]
	private ?string $catalogFirmIdentification = null;

	/** Company identification number. */
	#[Map('ID')]
	private string $id;

	public function __construct(string $id)
	{
		$this->setId($id);
	}

	public function getUserID(): ?string
	{
		return $this->userID;
	}

	public function setUserID(?string $userID): self
	{
		$this->userID = $userID;
		return $this;
	}

	public function getCatalogFirmIdentification(): ?string
	{
		return $this->catalogFirmIdentification;
	}

	public function setCatalogFirmIdentification(?string $catalogFirmIdentification): self
	{
		$this->catalogFirmIdentification = $catalogFirmIdentification;
		return $this;
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