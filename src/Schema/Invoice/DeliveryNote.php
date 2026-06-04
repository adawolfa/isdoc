<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use DateTimeInterface;

/**
 * Information about referenced delivery note.
 */
class DeliveryNote implements Entity
{

	use Backing;

	/** Private identifier of delivery note at supplier. */
	public string $id {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('ID');
		set {
			$this->node->setString('ID', $value);
		}
	}

	/** Issue date. */
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