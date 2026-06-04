<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;

/**
 * Information directly relating to an item.
 */
class Item implements Entity
{

	use Backing;

	/** Item description. */
	public ?string $description {
		get => $this->node->getString('Description');
		set {
			$this->node->setString('Description', $value);
		}
	}

	/** EAN code. */
	public ?CatalogueItemIdentification $catalogueItemIdentification {
		get => $this->node->getChild('CatalogueItemIdentification', CatalogueItemIdentification::class);
		set { $this->node->setChild('CatalogueItemIdentification', $value); }
	}

	/** Seller's item identification. */
	public ?SellersItemIdentification $sellersItemIdentification {
		get => $this->node->getChild('SellersItemIdentification', SellersItemIdentification::class);
		set { $this->node->setChild('SellersItemIdentification', $value); }
	}

	/** Secondary seller's item identification. */
	public ?SecondarySellersItemIdentification $secondarySellersItemIdentification {
		get => $this->node->getChild('SecondarySellersItemIdentification', SecondarySellersItemIdentification::class);
		set { $this->node->setChild('SecondarySellersItemIdentification', $value); }
	}

	/** Tertiary seller's item identification. */
	public ?TertiarySellersItemIdentification $tertiarySellersItemIdentification {
		get => $this->node->getChild('TertiarySellersItemIdentification', TertiarySellersItemIdentification::class);
		set { $this->node->setChild('TertiarySellersItemIdentification', $value); }
	}

	/** Buyer's item identification. */
	public ?BuyersItemIdentification $buyersItemIdentification {
		get => $this->node->getChild('BuyersItemIdentification', BuyersItemIdentification::class);
		set { $this->node->setChild('BuyersItemIdentification', $value); }
	}

	/** Batch or serial number collection. */
	public ?StoreBatches $storeBatches {
		get => $this->node->getChild('StoreBatches', StoreBatches::class);
		set { $this->node->setChild('StoreBatches', $value); }
	}

}