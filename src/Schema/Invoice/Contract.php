<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use DateTimeInterface;

/**
 * Information about related contract.
 */
class Contract implements Entity
{

	use Backing;

	/** Human readable contract identifier. */
	public string $id {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('ID');
		set {
			$this->node->setString('ID', $value);
		}
	}

	/** Contract identifier supplied at the time of contract registration inside file system. */
	public ?string $uuid {
		get => $this->node->getString('UUID');
		set {
			Restriction::pattern($value, '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}');
			$this->node->setString('UUID', $value);
		}
	}

	/** Date of contract signature. */
	public DateTimeInterface $issueDate {
		/** @throws Exception */
		get => $this->node->getDateOrThrow('IssueDate');
		set {
			$this->node->setDate('IssueDate', $value);
		}
	}

	/** Date until contract is valid. */
	public ?DateTimeInterface $lastValidDate {
		/** @throws Exception */
		get => $this->node->getDate('LastValidDate');
		set {
			$this->node->setDate('LastValidDate', $value);
		}
	}

	/** Contract for indefinite period. */
	public ?LastValidDateUnbounded $lastValidDateUnbounded {
		get => $this->node->getChild('LastValidDateUnbounded', LastValidDateUnbounded::class);
		set { $this->node->setChild('LastValidDateUnbounded', $value); }
	}

	/** Unique identifier inside ISDS system. */
	public ?string $isds_id {
		get => $this->node->getString('ISDS_ID');
		set {
			$this->node->setString('ISDS_ID', $value);
		}
	}

	/** File number. */
	public ?string $file {
		get => $this->node->getString('FileReference');
		set {
			$this->node->setString('FileReference', $value);
		}
	}

	/** Reference number. */
	public ?string $referenceNumber {
		get => $this->node->getString('ReferenceNumber');
		set {
			$this->node->setString('ReferenceNumber', $value);
		}
	}

	public function __construct(
		string $id,
		DateTimeInterface $issueDate,
	)
	{
		$this->id = $id;
		$this->issueDate = $issueDate;
	}

}