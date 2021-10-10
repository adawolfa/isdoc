<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use DateTimeImmutable;
use Nette\SmartObject;

/**
 * Information about referenced purchase order.
 *
 * @property string                 $salesOrderID
 * @property string|null            $externalOrderID
 * @property DateTimeImmutable|null $issueDate
 * @property DateTimeImmutable|null $externalOrderIssueDate
 * @property string|null            $uuid
 * @property string|null            $isds_id
 * @property string|null            $file
 * @property string|null            $referenceNumber
 */
class Order implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Private identifier of purchase order received at supplier.
	 *
	 * @Map("SalesOrderID")
	 */
	private string $salesOrderID;

	/**
	 * External number of accepted purchase order, usually purchase order issued at purchaser.
	 *
	 * @Map("ExternalOrderID")
	 */
	private ?string $externalOrderID = null;

	/**
	 * Issue date of purchase order received at supplier.
	 *
	 * @Map("IssueDate")
	 */
	private ?DateTimeImmutable $issueDate = null;

	/**
	 * Issue date of purchase order.
	 *
	 * @Map("ExternalOrderIssueDate")
	 */
	private ?DateTimeImmutable $externalOrderIssueDate = null;

	/**
	 * Unique GUID identifier.
	 *
	 * @Map("UUID")
	 */
	private ?string $uuid = null;

	/**
	 * Message ID inside ISDS system.
	 *
	 * @Map("ISDS_ID")
	 */
	private ?string $isds_id = null;

	/**
	 * File number.
	 *
	 * @Map("FileReference")
	 */
	private ?string $file = null;

	/**
	 * Reference number.
	 *
	 * @Map("ReferenceNumber")
	 */
	private ?string $referenceNumber = null;

	public function __construct(string $salesOrderID)
	{
		$this->setSalesOrderID($salesOrderID);
	}

	public function getSalesOrderID(): string
	{
		return $this->salesOrderID;
	}

	public function setSalesOrderID(string $salesOrderID): self
	{
		$this->salesOrderID = $salesOrderID;
		return $this;
	}

	public function getExternalOrderID(): ?string
	{
		return $this->externalOrderID;
	}

	public function setExternalOrderID(?string $externalOrderID): self
	{
		$this->externalOrderID = $externalOrderID;
		return $this;
	}

	public function getIssueDate(): ?DateTimeImmutable
	{
		return $this->issueDate;
	}

	public function setIssueDate(?DateTimeImmutable $issueDate): self
	{
		$this->issueDate = $issueDate;
		return $this;
	}

	public function getExternalOrderIssueDate(): ?DateTimeImmutable
	{
		return $this->externalOrderIssueDate;
	}

	public function setExternalOrderIssueDate(?DateTimeImmutable $externalOrderIssueDate): self
	{
		$this->externalOrderIssueDate = $externalOrderIssueDate;
		return $this;
	}

	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	public function setUuid(?string $uuid): self
	{
		Restriction::pattern($uuid, '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}');
		$this->uuid = $uuid;
		return $this;
	}

	public function getIsds_id(): ?string
	{
		return $this->isds_id;
	}

	public function setIsds_id(?string $isds_id): self
	{
		$this->isds_id = $isds_id;
		return $this;
	}

	public function getFile(): ?string
	{
		return $this->file;
	}

	public function setFile(?string $file): self
	{
		$this->file = $file;
		return $this;
	}

	public function getReferenceNumber(): ?string
	{
		return $this->referenceNumber;
	}

	public function setReferenceNumber(?string $referenceNumber): self
	{
		$this->referenceNumber = $referenceNumber;
		return $this;
	}

}