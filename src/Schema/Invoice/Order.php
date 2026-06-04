<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use DateTimeInterface;

/**
 * Information about referenced purchase order.
 */
class Order implements Entity
{

	use Backing;

	/** Private identifier of purchase order received at supplier. */
	public string $salesOrderID {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('SalesOrderID');
		set {
			$this->node->setString('SalesOrderID', $value);
		}
	}

	/** External number of accepted purchase order, usually purchase order issued at purchaser. */
	public ?string $externalOrderID {
		get => $this->node->getString('ExternalOrderID');
		set {
			$this->node->setString('ExternalOrderID', $value);
		}
	}

	/** Issue date of purchase order received at supplier. */
	public ?DateTimeInterface $issueDate {
		/** @throws Exception */
		get => $this->node->getDate('IssueDate');
		set {
			$this->node->setDate('IssueDate', $value);
		}
	}

	/** Issue date of purchase order. */
	public ?DateTimeInterface $externalOrderIssueDate {
		/** @throws Exception */
		get => $this->node->getDate('ExternalOrderIssueDate');
		set {
			$this->node->setDate('ExternalOrderIssueDate', $value);
		}
	}

	/** Unique GUID identifier. */
	public ?string $uuid {
		get => $this->node->getString('UUID');
		set {
			Restriction::pattern($value, '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}');
			$this->node->setString('UUID', $value);
		}
	}

	/** Message ID inside ISDS system. */
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
		string $salesOrderID,
	)
	{
		$this->salesOrderID = $salesOrderID;
	}

}