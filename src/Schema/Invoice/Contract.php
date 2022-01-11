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
 * Information about related contract.
 *
 * @property string                      $id
 * @property string|null                 $uuid
 * @property DateTimeImmutable           $issueDate
 * @property DateTimeImmutable|null      $lastValidDate
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
	private DateTimeImmutable $issueDate;

	/** Date until contract is valid. */
	#[Map('LastValidDate')]
	private ?DateTimeImmutable $lastValidDate = null;

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

	public function __construct(string $id, DateTimeImmutable $issueDate)
	{
		$this->setId($id);
		$this->setIssueDate($issueDate);
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

	public function getIssueDate(): DateTimeImmutable
	{
		return $this->issueDate;
	}

	public function setIssueDate(DateTimeImmutable $issueDate): self
	{
		$this->issueDate = $issueDate;
		return $this;
	}

	public function getLastValidDate(): ?DateTimeImmutable
	{
		return $this->lastValidDate;
	}

	public function setLastValidDate(?DateTimeImmutable $lastValidDate): self
	{
		$this->lastValidDate = $lastValidDate;
		return $this;
	}

	public function getLastValidDateUnbounded(): ?LastValidDateUnbounded
	{
		return $this->lastValidDateUnbounded;
	}

	public function setLastValidDateUnbounded(?LastValidDateUnbounded $lastValidDateUnbounded): self
	{
		$this->lastValidDateUnbounded = $lastValidDateUnbounded;
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