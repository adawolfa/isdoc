<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use DateTimeInterface;
use Nette\SmartObject;

/**
 * Information about referenced purchase order.
 *
 * @property string                 $salesOrderID
 * @property string|null            $externalOrderID
 * @property DateTimeInterface|null $issueDate
 * @property DateTimeInterface|null $externalOrderIssueDate
 * @property string|null            $uuid
 * @property string|null            $isds_id
 * @property string|null            $file
 * @property string|null            $referenceNumber
 */
class Order implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Private identifier of purchase order received at supplier. */
	#[Map('SalesOrderID')]
	private string $salesOrderID;

	/** External number of accepted purchase order, usually purchase order issued at purchaser. */
	#[Map('ExternalOrderID')]
	private ?string $externalOrderID = null;

	/** Issue date of purchase order received at supplier. */
	#[Map('IssueDate')]
	private ?DateTimeInterface $issueDate = null;

	/** Issue date of purchase order. */
	#[Map('ExternalOrderIssueDate')]
	private ?DateTimeInterface $externalOrderIssueDate = null;

	/** Unique GUID identifier. */
	#[Map('UUID')]
	private ?string $uuid = null;

	/** Message ID inside ISDS system. */
	#[Map('ISDS_ID')]
	private ?string $isds_id = null;

	/** File number. */
	#[Map('FileReference')]
	private ?string $file = null;

	/** Reference number. */
	#[Map('ReferenceNumber')]
	private ?string $referenceNumber = null;

	public function __construct(string $salesOrderID)
	{
		$this->setSalesOrderID($salesOrderID);
	}

	/** @deprecated Method accessors are deprecated, use {@see $salesOrderID} property instead. */
	public function getSalesOrderID(): string
	{
		return $this->salesOrderID;
	}

	/** @deprecated Method accessors are deprecated, use {@see $salesOrderID} property instead. */
	public function setSalesOrderID(string $salesOrderID): self
	{
		$this->salesOrderID = $salesOrderID;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $externalOrderID} property instead. */
	public function getExternalOrderID(): ?string
	{
		return $this->externalOrderID;
	}

	/** @deprecated Method accessors are deprecated, use {@see $externalOrderID} property instead. */
	public function setExternalOrderID(?string $externalOrderID): self
	{
		$this->externalOrderID = $externalOrderID;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $issueDate} property instead. */
	public function getIssueDate(): ?DateTimeInterface
	{
		return $this->issueDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $issueDate} property instead. */
	public function setIssueDate(?DateTimeInterface $issueDate): self
	{
		$this->issueDate = $issueDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $externalOrderIssueDate} property instead. */
	public function getExternalOrderIssueDate(): ?DateTimeInterface
	{
		return $this->externalOrderIssueDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $externalOrderIssueDate} property instead. */
	public function setExternalOrderIssueDate(?DateTimeInterface $externalOrderIssueDate): self
	{
		$this->externalOrderIssueDate = $externalOrderIssueDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $uuid} property instead. */
	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	/** @deprecated Method accessors are deprecated, use {@see $uuid} property instead. */
	public function setUuid(?string $uuid): self
	{
		Restriction::pattern($uuid, '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}');
		$this->uuid = $uuid;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $isds_id} property instead. */
	public function getIsds_id(): ?string
	{
		return $this->isds_id;
	}

	/** @deprecated Method accessors are deprecated, use {@see $isds_id} property instead. */
	public function setIsds_id(?string $isds_id): self
	{
		$this->isds_id = $isds_id;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $file} property instead. */
	public function getFile(): ?string
	{
		return $this->file;
	}

	/** @deprecated Method accessors are deprecated, use {@see $file} property instead. */
	public function setFile(?string $file): self
	{
		$this->file = $file;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $referenceNumber} property instead. */
	public function getReferenceNumber(): ?string
	{
		return $this->referenceNumber;
	}

	/** @deprecated Method accessors are deprecated, use {@see $referenceNumber} property instead. */
	public function setReferenceNumber(?string $referenceNumber): self
	{
		$this->referenceNumber = $referenceNumber;
		return $this;
	}

}