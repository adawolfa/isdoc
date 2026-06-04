<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use DateTimeInterface;
use Nette\SmartObject;

/**
 * Information about referenced delivery note.
 *
 * @property string                 $id
 * @property DateTimeInterface|null $issueDate
 * @property string|null            $uuid
 */
class DeliveryNote implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Private identifier of delivery note at supplier. */
	#[Map('ID')]
	private string $id;

	/** Issue date. */
	#[Map('IssueDate')]
	private ?DateTimeInterface $issueDate = null;

	/** Unique GUID identifier. */
	#[Map('UUID')]
	private ?string $uuid = null;

	public function __construct(string $id)
	{
		$this->setId($id);
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

}