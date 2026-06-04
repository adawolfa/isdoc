<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use DateTimeInterface;

/**
 * Batch/serial number.
 */
class StoreBatch implements Entity
{

	use Backing;

	/** Batch name/serial number. */
	public string $name {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('Name');
		set {
			$this->node->setString('Name', $value);
		}
	}

	/** Note. */
	public ?Note $note {
		get => $this->node->getChild('Note', Note::class);
		set { $this->node->setChild('Note', $value); }
	}

	/** Expiration date. */
	public ?DateTimeInterface $expirationDate {
		/** @throws Exception */
		get => $this->node->getDate('ExpirationDate');
		set {
			$this->node->setDate('ExpirationDate', $value);
		}
	}

	/** Specification. */
	public ?string $specification {
		get => $this->node->getString('Specification');
		set {
			$this->node->setString('Specification', $value);
		}
	}

	/** Amount. */
	public Quantity $quantity {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('Quantity', Quantity::class);
		set { $this->node->setChild('Quantity', $value); }
	}

	/** Differentiation between batch and serial number. */
	public BatchOrSerialNumber $batchOrSerialNumber {
		/** @throws Exception */
		get => $this->node->getEnumOrThrow('BatchOrSerialNumber', BatchOrSerialNumber::class);
		set { $this->node->setEnum('BatchOrSerialNumber', $value); }
	}

	/** External number of duty stamp. */
	public ?string $sealSeriesID {
		get => $this->node->getString('SealSeriesID');
		set {
			$this->node->setString('SealSeriesID', $value);
		}
	}

	public function __construct(
		string $name,
		Quantity $quantity,
		BatchOrSerialNumber $batchOrSerialNumber,
	)
	{
		$this->name = $name;
		$this->quantity = $quantity;
		$this->batchOrSerialNumber = $batchOrSerialNumber;
	}

}