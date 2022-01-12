<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use DateTimeInterface;
use Nette\SmartObject;

/**
 * Batch/serial number.
 *
 * @property string                 $name
 * @property Note|null              $note
 * @property DateTimeInterface|null $expirationDate
 * @property string|null            $specification
 * @property Quantity               $quantity
 * @property string                 $batchOrSerialNumber
 * @property string|null            $sealSeriesID
 */
class StoreBatch implements Arrayable
{

	use SmartObject;
	use ToArray;

	public const BATCH_OR_SERIAL_NUMBER_BATCH         = 'B';
	public const BATCH_OR_SERIAL_NUMBER_SERIAL_NUMBER = 'S';

	/** Batch name/serial number. */
	#[Map('Name')]
	private string $name;

	/** Note. */
	#[Map('Note')]
	private ?Note $note = null;

	/** Expiration date. */
	#[Map('ExpirationDate')]
	private ?DateTimeInterface $expirationDate = null;

	/** Specification. */
	#[Map('Specification')]
	private ?string $specification = null;

	/** Amount. */
	#[Map('Quantity')]
	private Quantity $quantity;

	/** Differentiation between batch and serial number. */
	#[Map('BatchOrSerialNumber')]
	private string $batchOrSerialNumber;

	/** External number of duty stamp. */
	#[Map('SealSeriesID')]
	private ?string $sealSeriesID = null;

	public function __construct(string $name, Quantity $quantity, string $batchOrSerialNumber)
	{
		$this->setName($name);
		$this->setQuantity($quantity);
		$this->setBatchOrSerialNumber($batchOrSerialNumber);
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	public function getNote(): ?Note
	{
		return $this->note;
	}

	public function setNote(?Note $note): self
	{
		$this->note = $note;
		return $this;
	}

	public function getExpirationDate(): ?DateTimeInterface
	{
		return $this->expirationDate;
	}

	public function setExpirationDate(?DateTimeInterface $expirationDate): self
	{
		$this->expirationDate = $expirationDate;
		return $this;
	}

	public function getSpecification(): ?string
	{
		return $this->specification;
	}

	public function setSpecification(?string $specification): self
	{
		$this->specification = $specification;
		return $this;
	}

	public function getQuantity(): Quantity
	{
		return $this->quantity;
	}

	public function setQuantity(Quantity $quantity): self
	{
		$this->quantity = $quantity;
		return $this;
	}

	public function getBatchOrSerialNumber(): string
	{
		return $this->batchOrSerialNumber;
	}

	public function setBatchOrSerialNumber(string $batchOrSerialNumber): self
	{
		Restriction::enumeration($batchOrSerialNumber, [
			self::BATCH_OR_SERIAL_NUMBER_BATCH,
			self::BATCH_OR_SERIAL_NUMBER_SERIAL_NUMBER,
		]);
		$this->batchOrSerialNumber = $batchOrSerialNumber;
		return $this;
	}

	public function getSealSeriesID(): ?string
	{
		return $this->sealSeriesID;
	}

	public function setSealSeriesID(?string $sealSeriesID): self
	{
		$this->sealSeriesID = $sealSeriesID;
		return $this;
	}

}