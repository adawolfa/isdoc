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
 * Reference to an original document which is being corrected by this document (only for document types 2, 3 and 6).
 *
 * @property string                 $id
 * @property DateTimeImmutable|null $issueDate
 * @property string|null            $uuid
 */
class OriginalDocument implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Human readable number of original document.
	 *
	 * @Map("ID")
	 */
	private string $id;

	/**
	 * Issue date of original document.
	 *
	 * @Map("IssueDate")
	 */
	private ?DateTimeImmutable $issueDate = null;

	/**
	 * Unique GUID identifier.
	 *
	 * @Map("UUID")
	 */
	private ?string $uuid = null;

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

	public function getIssueDate(): ?DateTimeImmutable
	{
		return $this->issueDate;
	}

	public function setIssueDate(?DateTimeImmutable $issueDate): self
	{
		$this->issueDate = $issueDate;
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

}