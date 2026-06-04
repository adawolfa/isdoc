<?php declare(strict_types=1);

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

	/** @deprecated use {@see BatchOrSerialNumber::Batch} instead */
	public const string BATCH_OR_SERIAL_NUMBER_BATCH = BatchOrSerialNumber::Batch;

	/** @deprecated use {@see BatchOrSerialNumber::SerialNumber} instead */
	public const string BATCH_OR_SERIAL_NUMBER_SERIAL_NUMBER = BatchOrSerialNumber::SerialNumber;

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

	/** @deprecated Method accessors are deprecated, use {@see $name} property instead. */
	public function getName(): string
	{
		return $this->name;
	}

	/** @deprecated Method accessors are deprecated, use {@see $name} property instead. */
	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $note} property instead. */
	public function getNote(): ?Note
	{
		return $this->note;
	}

	/** @deprecated Method accessors are deprecated, use {@see $note} property instead. */
	public function setNote(?Note $note): self
	{
		$this->note = $note;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $expirationDate} property instead. */
	public function getExpirationDate(): ?DateTimeInterface
	{
		return $this->expirationDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $expirationDate} property instead. */
	public function setExpirationDate(?DateTimeInterface $expirationDate): self
	{
		$this->expirationDate = $expirationDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $specification} property instead. */
	public function getSpecification(): ?string
	{
		return $this->specification;
	}

	/** @deprecated Method accessors are deprecated, use {@see $specification} property instead. */
	public function setSpecification(?string $specification): self
	{
		$this->specification = $specification;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $quantity} property instead. */
	public function getQuantity(): Quantity
	{
		return $this->quantity;
	}

	/** @deprecated Method accessors are deprecated, use {@see $quantity} property instead. */
	public function setQuantity(Quantity $quantity): self
	{
		$this->quantity = $quantity;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $batchOrSerialNumber} property instead. */
	public function getBatchOrSerialNumber(): string
	{
		return $this->batchOrSerialNumber;
	}

	/** @deprecated Method accessors are deprecated, use {@see $batchOrSerialNumber} property instead. */
	public function setBatchOrSerialNumber(string $batchOrSerialNumber): self
	{
		Restriction::enumeration($batchOrSerialNumber, [
			BatchOrSerialNumber::Batch,
			BatchOrSerialNumber::SerialNumber,
		]);
		$this->batchOrSerialNumber = $batchOrSerialNumber;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $sealSeriesID} property instead. */
	public function getSealSeriesID(): ?string
	{
		return $this->sealSeriesID;
	}

	/** @deprecated Method accessors are deprecated, use {@see $sealSeriesID} property instead. */
	public function setSealSeriesID(?string $sealSeriesID): self
	{
		$this->sealSeriesID = $sealSeriesID;
		return $this;
	}

}