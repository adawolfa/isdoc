<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use DateTimeInterface;

/**
 * Reference to an original document which is being corrected by this document (only for document types 2, 3 and 6).
 */
class OriginalDocument implements Entity
{

	use Backing;

	/** Human readable number of original document. */
	public string $id {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('ID');
		set {
			$this->node->setString('ID', $value);
		}
	}

	/** Issue date of original document. */
	public ?DateTimeInterface $issueDate {
		/** @throws Exception */
		get => $this->node->getDate('IssueDate');
		set {
			$this->node->setDate('IssueDate', $value);
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

	public function __construct(
		string $id,
	)
	{
		$this->id = $id;
	}

}