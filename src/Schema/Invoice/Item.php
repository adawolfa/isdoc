<?php

declare(strict_types=1);
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

	/**
	 * Item description.
	 *
	 * @Map("Description")
	 */
	private ?string $description = null;

	/**
	 * EAN code.
	 *
	 * @Map("CatalogueItemIdentification")
	 */
	private ?CatalogueItemIdentification $catalogueItemIdentification = null;

	/**
	 * Seller's item identification.
	 *
	 * @Map("SellersItemIdentification")
	 */
	private ?SellersItemIdentification $sellersItemIdentification = null;

	/**
	 * Secondary seller's item identification.
	 *
	 * @Map("SecondarySellersItemIdentification")
	 */
	private ?SecondarySellersItemIdentification $secondarySellersItemIdentification = null;

	/**
	 * Tertiary seller's item identification.
	 *
	 * @Map("TertiarySellersItemIdentification")
	 */
	private ?TertiarySellersItemIdentification $tertiarySellersItemIdentification = null;

	/**
	 * Buyer's item identification.
	 *
	 * @Map("BuyersItemIdentification")
	 */
	private ?BuyersItemIdentification $buyersItemIdentification = null;

	/**
	 * Batch or serial number collection.
	 *
	 * @Map("StoreBatches")
	 */
	private ?StoreBatches $storeBatches = null;

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): self
	{
		$this->description = $description;
		return $this;
	}

	public function getCatalogueItemIdentification(): ?CatalogueItemIdentification
	{
		return $this->catalogueItemIdentification;
	}

	public function setCatalogueItemIdentification(?CatalogueItemIdentification $catalogueItemIdentification): self
	{
		$this->catalogueItemIdentification = $catalogueItemIdentification;
		return $this;
	}

	public function getSellersItemIdentification(): ?SellersItemIdentification
	{
		return $this->sellersItemIdentification;
	}

	public function setSellersItemIdentification(?SellersItemIdentification $sellersItemIdentification): self
	{
		$this->sellersItemIdentification = $sellersItemIdentification;
		return $this;
	}

	public function getSecondarySellersItemIdentification(): ?SecondarySellersItemIdentification
	{
		return $this->secondarySellersItemIdentification;
	}

	public function setSecondarySellersItemIdentification(?SecondarySellersItemIdentification $secondarySellersItemIdentification): self
	{
		$this->secondarySellersItemIdentification = $secondarySellersItemIdentification;
		return $this;
	}

	public function getTertiarySellersItemIdentification(): ?TertiarySellersItemIdentification
	{
		return $this->tertiarySellersItemIdentification;
	}

	public function setTertiarySellersItemIdentification(?TertiarySellersItemIdentification $tertiarySellersItemIdentification): self
	{
		$this->tertiarySellersItemIdentification = $tertiarySellersItemIdentification;
		return $this;
	}

	public function getBuyersItemIdentification(): ?BuyersItemIdentification
	{
		return $this->buyersItemIdentification;
	}

	public function setBuyersItemIdentification(?BuyersItemIdentification $buyersItemIdentification): self
	{
		$this->buyersItemIdentification = $buyersItemIdentification;
		return $this;
	}

	public function getStoreBatches(): ?StoreBatches
	{
		return $this->storeBatches;
	}

	public function setStoreBatches(?StoreBatches $storeBatches): self
	{
		$this->storeBatches = $storeBatches;
		return $this;
	}

}