<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use DateTimeInterface;
use Nette\SmartObject;

/**
 * Information about related contract.
 *
 * @property string                      $id
 * @property string|null                 $uuid
 * @property DateTimeInterface           $issueDate
 * @property DateTimeInterface|null      $lastValidDate
 * @property LastValidDateUnbounded|null $lastValidDateUnbounded
 * @property string|null                 $isds_id
 * @property string|null                 $file
 * @property string|null                 $referenceNumber
 */
class Contract implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Human readable contract identifier. */
	#[Map('ID')]
	private string $id;

	/** Contract identifier supplied at the time of contract registration inside file system. */
	#[Map('UUID')]
	private ?string $uuid = null;

	/** Date of contract signature. */
	#[Map('IssueDate')]
	private DateTimeInterface $issueDate;

	/** Date until contract is valid. */
	#[Map('LastValidDate')]
	private ?DateTimeInterface $lastValidDate = null;

	/** Contract for indefinite period. */
	#[Map('LastValidDateUnbounded')]
	private ?LastValidDateUnbounded $lastValidDateUnbounded = null;

	/** Unique identifier inside ISDS system. */
	#[Map('ISDS_ID')]
	private ?string $isds_id = null;

	/** File number. */
	#[Map('FileReference')]
	private ?string $file = null;

	/** Reference number. */
	#[Map('ReferenceNumber')]
	private ?string $referenceNumber = null;

	public function __construct(string $id, DateTimeInterface $issueDate)
	{
		$this->setId($id);
		$this->setIssueDate($issueDate);
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function getId(): string
	{
		return $this->id;
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function setId(string $id): self
	{
		$this->id = $id;
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

	/** @deprecated Method accessors are deprecated, use {@see $issueDate} property instead. */
	public function getIssueDate(): DateTimeInterface
	{
		return $this->issueDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $issueDate} property instead. */
	public function setIssueDate(DateTimeInterface $issueDate): self
	{
		$this->issueDate = $issueDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lastValidDate} property instead. */
	public function getLastValidDate(): ?DateTimeInterface
	{
		return $this->lastValidDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lastValidDate} property instead. */
	public function setLastValidDate(?DateTimeInterface $lastValidDate): self
	{
		$this->lastValidDate = $lastValidDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lastValidDateUnbounded} property instead. */
	public function getLastValidDateUnbounded(): ?LastValidDateUnbounded
	{
		return $this->lastValidDateUnbounded;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lastValidDateUnbounded} property instead. */
	public function setLastValidDateUnbounded(?LastValidDateUnbounded $lastValidDateUnbounded): self
	{
		$this->lastValidDateUnbounded = $lastValidDateUnbounded;
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