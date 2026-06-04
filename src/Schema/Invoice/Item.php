<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information directly relating to an item.
 *
 * @property string|null                             $description
 * @property CatalogueItemIdentification|null        $catalogueItemIdentification
 * @property SellersItemIdentification|null          $sellersItemIdentification
 * @property SecondarySellersItemIdentification|null $secondarySellersItemIdentification
 * @property TertiarySellersItemIdentification|null  $tertiarySellersItemIdentification
 * @property BuyersItemIdentification|null           $buyersItemIdentification
 * @property StoreBatches|null                       $storeBatches
 */
class Item implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Item description. */
	#[Map('Description')]
	private ?string $description = null;

	/** EAN code. */
	#[Map('CatalogueItemIdentification')]
	private ?CatalogueItemIdentification $catalogueItemIdentification = null;

	/** Seller's item identification. */
	#[Map('SellersItemIdentification')]
	private ?SellersItemIdentification $sellersItemIdentification = null;

	/** Secondary seller's item identification. */
	#[Map('SecondarySellersItemIdentification')]
	private ?SecondarySellersItemIdentification $secondarySellersItemIdentification = null;

	/** Tertiary seller's item identification. */
	#[Map('TertiarySellersItemIdentification')]
	private ?TertiarySellersItemIdentification $tertiarySellersItemIdentification = null;

	/** Buyer's item identification. */
	#[Map('BuyersItemIdentification')]
	private ?BuyersItemIdentification $buyersItemIdentification = null;

	/** Batch or serial number collection. */
	#[Map('StoreBatches')]
	private ?StoreBatches $storeBatches = null;

	/** @deprecated Method accessors are deprecated, use {@see $description} property instead. */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/** @deprecated Method accessors are deprecated, use {@see $description} property instead. */
	public function setDescription(?string $description): self
	{
		$this->description = $description;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $catalogueItemIdentification} property instead. */
	public function getCatalogueItemIdentification(): ?CatalogueItemIdentification
	{
		return $this->catalogueItemIdentification;
	}

	/** @deprecated Method accessors are deprecated, use {@see $catalogueItemIdentification} property instead. */
	public function setCatalogueItemIdentification(?CatalogueItemIdentification $catalogueItemIdentification): self
	{
		$this->catalogueItemIdentification = $catalogueItemIdentification;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $sellersItemIdentification} property instead. */
	public function getSellersItemIdentification(): ?SellersItemIdentification
	{
		return $this->sellersItemIdentification;
	}

	/** @deprecated Method accessors are deprecated, use {@see $sellersItemIdentification} property instead. */
	public function setSellersItemIdentification(?SellersItemIdentification $sellersItemIdentification): self
	{
		$this->sellersItemIdentification = $sellersItemIdentification;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $secondarySellersItemIdentification} property instead. */
	public function getSecondarySellersItemIdentification(): ?SecondarySellersItemIdentification
	{
		return $this->secondarySellersItemIdentification;
	}

	/** @deprecated Method accessors are deprecated, use {@see $secondarySellersItemIdentification} property instead. */
	public function setSecondarySellersItemIdentification(
		?SecondarySellersItemIdentification $secondarySellersItemIdentification,
	): self
	{
		$this->secondarySellersItemIdentification = $secondarySellersItemIdentification;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $tertiarySellersItemIdentification} property instead. */
	public function getTertiarySellersItemIdentification(): ?TertiarySellersItemIdentification
	{
		return $this->tertiarySellersItemIdentification;
	}

	/** @deprecated Method accessors are deprecated, use {@see $tertiarySellersItemIdentification} property instead. */
	public function setTertiarySellersItemIdentification(
		?TertiarySellersItemIdentification $tertiarySellersItemIdentification,
	): self
	{
		$this->tertiarySellersItemIdentification = $tertiarySellersItemIdentification;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $buyersItemIdentification} property instead. */
	public function getBuyersItemIdentification(): ?BuyersItemIdentification
	{
		return $this->buyersItemIdentification;
	}

	/** @deprecated Method accessors are deprecated, use {@see $buyersItemIdentification} property instead. */
	public function setBuyersItemIdentification(?BuyersItemIdentification $buyersItemIdentification): self
	{
		$this->buyersItemIdentification = $buyersItemIdentification;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $storeBatches} property instead. */
	public function getStoreBatches(): ?StoreBatches
	{
		return $this->storeBatches;
	}

	/** @deprecated Method accessors are deprecated, use {@see $storeBatches} property instead. */
	public function setStoreBatches(?StoreBatches $storeBatches): self
	{
		$this->storeBatches = $storeBatches;
		return $this;
	}

}