<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;

/**
 * Information about an invoice line.
 */
class InvoiceLine implements Entity
{

	use Backing;

	/** Unique alphanumeric line identifier. */
	public string $id {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('ID');
		set {
			Restriction::maxLength($value, 36);
			$this->node->setString('ID', $value);
		}
	}

	/** Reference to line on a related purchase order. */
	public ?OrderLine $order {
		get => $this->node->getChild('OrderReference', OrderLine::class);
		set { $this->node->setChild('OrderReference', $value); }
	}

	/** Information about referenced line on delivery note. */
	public ?DeliveryNoteLine $deliveryNote {
		get => $this->node->getChild('DeliveryNoteReference', DeliveryNoteLine::class);
		set { $this->node->setChild('DeliveryNoteReference', $value); }
	}

	/** Line reference to an original document which is being corrected by this document (only for document types 2, 3 and 6). */
	public ?OriginalDocumentLine $originalDocument {
		get => $this->node->getChild('OriginalDocumentReference', OriginalDocumentLine::class);
		set { $this->node->setChild('OriginalDocumentReference', $value); }
	}

	/** Reference to a related contract. */
	public ?ContractLine $contract {
		get => $this->node->getChild('ContractReference', ContractLine::class);
		set { $this->node->setChild('ContractReference', $value); }
	}

	/** Egoverment accounting classifier. */
	public ?string $egovClassifier {
		get => $this->node->getString('EgovClassifier');
		set {
			$this->node->setString('EgovClassifier', $value);
		}
	}

	/** Invoiced quantity. */
	public ?Quantity $invoicedQuantity {
		get => $this->node->getChild('InvoicedQuantity', Quantity::class);
		set { $this->node->setChild('InvoicedQuantity', $value); }
	}

	/** Total line amount without tax in a foreign currency. */
	public ?Number $lineExtensionAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('LineExtensionAmountCurr');
		set {
			$this->node->setNumber('LineExtensionAmountCurr', $value);
		}
	}

	/** Total line amount without tax in a local currency. */
	public Number $lineExtensionAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('LineExtensionAmount');
		set {
			$this->node->setNumber('LineExtensionAmount', $value);
		}
	}

	/** Total line amount without tax in a local currency without discount. */
	public ?Number $lineExtensionAmountBeforeDiscount {
		/** @throws Exception */
		get => $this->node->getNumber('LineExtensionAmountBeforeDiscount');
		set {
			$this->node->setNumber('LineExtensionAmountBeforeDiscount', $value);
		}
	}

	/** Total line amount including tax in a foreign currency. */
	public ?Number $lineExtensionAmountTaxInclusiveCurr {
		/** @throws Exception */
		get => $this->node->getNumber('LineExtensionAmountTaxInclusiveCurr');
		set {
			$this->node->setNumber('LineExtensionAmountTaxInclusiveCurr', $value);
		}
	}

	/** Total line amount including tax in a local currency. */
	public Number $lineExtensionAmountTaxInclusive {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('LineExtensionAmountTaxInclusive');
		set {
			$this->node->setNumber('LineExtensionAmountTaxInclusive', $value);
		}
	}

	/** Total line amount including tax in a local currency without discount. */
	public ?Number $lineExtensionAmountTaxInclusiveBeforeDiscount {
		/** @throws Exception */
		get => $this->node->getNumber('LineExtensionAmountTaxInclusiveBeforeDiscount');
		set {
			$this->node->setNumber('LineExtensionAmountTaxInclusiveBeforeDiscount', $value);
		}
	}

	/** Line tax amount in a local currency. */
	public Number $lineExtensionTaxAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('LineExtensionTaxAmount');
		set {
			$this->node->setNumber('LineExtensionTaxAmount', $value);
		}
	}

	/** Unit price without tax in a local currency. */
	public Number $unitPrice {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('UnitPrice');
		set {
			$this->node->setNumber('UnitPrice', $value);
		}
	}

	/** Unit price including tax in a local currency. */
	public Number $unitPriceTaxInclusive {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('UnitPriceTaxInclusive');
		set {
			$this->node->setNumber('UnitPriceTaxInclusive', $value);
		}
	}

	/** Compound VAT field. */
	public ClassifiedTaxCategory $classifiedTaxCategory {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('ClassifiedTaxCategory', ClassifiedTaxCategory::class);
		set { $this->node->setChild('ClassifiedTaxCategory', $value); }
	}

	/** Note. */
	public ?Note $note {
		get => $this->node->getChild('Note', Note::class);
		set { $this->node->setChild('Note', $value); }
	}

	/** Legislation citation which defines VAT exception for this line. */
	public ?Note $vatNote {
		get => $this->node->getChild('VATNote', Note::class);
		set { $this->node->setChild('VATNote', $value); }
	}

	/** Information directly relating to an item. */
	public ?Item $item {
		get => $this->node->getChild('Item', Item::class);
		set { $this->node->setChild('Item', $value); }
	}

	/** Arbitrary fragment of user-defined elements. Elements must use their own namespace. */
	public ?Extensions $extensions {
		get => $this->node->getChild('Extensions', Extensions::class);
		set { $this->node->setChild('Extensions', $value); }
	}

	public function __construct(
		string $id,
		Number $lineExtensionAmount,
		Number $lineExtensionAmountTaxInclusive,
		Number $lineExtensionTaxAmount,
		Number $unitPrice,
		Number $unitPriceTaxInclusive,
		ClassifiedTaxCategory $classifiedTaxCategory,
	)
	{
		$this->id = $id;
		$this->lineExtensionAmount = $lineExtensionAmount;
		$this->lineExtensionAmountTaxInclusive = $lineExtensionAmountTaxInclusive;
		$this->lineExtensionTaxAmount = $lineExtensionTaxAmount;
		$this->unitPrice = $unitPrice;
		$this->unitPriceTaxInclusive = $unitPriceTaxInclusive;
		$this->classifiedTaxCategory = $classifiedTaxCategory;
	}

}