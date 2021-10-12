<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about an invoice line.
 *
 * @property string                    $id
 * @property OrderLine|null            $order
 * @property DeliveryNoteLine|null     $deliveryNote
 * @property OriginalDocumentLine|null $originalDocument
 * @property ContractLine|null         $contract
 * @property string|null               $egovClassifier
 * @property Quantity|null             $invoicedQuantity
 * @property string|null               $lineExtensionAmountCurr
 * @property string                    $lineExtensionAmount
 * @property string|null               $lineExtensionAmountBeforeDiscount
 * @property string|null               $lineExtensionAmountTaxInclusiveCurr
 * @property string                    $lineExtensionAmountTaxInclusive
 * @property string|null               $lineExtensionAmountTaxInclusiveBeforeDiscount
 * @property string                    $lineExtensionTaxAmount
 * @property string                    $unitPrice
 * @property string                    $unitPriceTaxInclusive
 * @property ClassifiedTaxCategory     $classifiedTaxCategory
 * @property Note|null                 $note
 * @property Note|null                 $vatNote
 * @property Item|null                 $item
 */
class InvoiceLine implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Unique alphanumeric line identifier.
	 *
	 * @Map("ID")
	 */
	private string $id;

	/**
	 * Reference to line on a related purchase order.
	 *
	 * @Map("OrderReference")
	 */
	private ?OrderLine $order = null;

	/**
	 * Information about referenced line on delivery note.
	 *
	 * @Map("DeliveryNoteReference")
	 */
	private ?DeliveryNoteLine $deliveryNote = null;

	/**
	 * Line reference to an original document which is being corrected by this document (only for document types 2, 3 and 6).
	 *
	 * @Map("OriginalDocumentReference")
	 */
	private ?OriginalDocumentLine $originalDocument = null;

	/**
	 * Reference to a related contract.
	 *
	 * @Map("ContractReference")
	 */
	private ?ContractLine $contract = null;

	/**
	 * Egoverment accounting classifier.
	 *
	 * @Map("EgovClassifier")
	 */
	private ?string $egovClassifier = null;

	/**
	 * Invoiced quantity.
	 *
	 * @Map("InvoicedQuantity")
	 */
	private ?Quantity $invoicedQuantity = null;

	/**
	 * Total line amount without tax in a foreign currency.
	 *
	 * @Map("LineExtensionAmountCurr")
	 */
	private ?string $lineExtensionAmountCurr = null;

	/**
	 * Total line amount without tax in a local currency.
	 *
	 * @Map("LineExtensionAmount")
	 */
	private string $lineExtensionAmount;

	/**
	 * Total line amount without tax in a local currency without discount.
	 *
	 * @Map("LineExtensionAmountBeforeDiscount")
	 */
	private ?string $lineExtensionAmountBeforeDiscount = null;

	/**
	 * Total line amount including tax in a foreign currency.
	 *
	 * @Map("LineExtensionAmountTaxInclusiveCurr")
	 */
	private ?string $lineExtensionAmountTaxInclusiveCurr = null;

	/**
	 * Total line amount including tax in a local currency.
	 *
	 * @Map("LineExtensionAmountTaxInclusive")
	 */
	private string $lineExtensionAmountTaxInclusive;

	/**
	 * Total line amount including tax in a local currency without discount.
	 *
	 * @Map("LineExtensionAmountTaxInclusiveBeforeDiscount")
	 */
	private ?string $lineExtensionAmountTaxInclusiveBeforeDiscount = null;

	/**
	 * Line tax amount in a local currency.
	 *
	 * @Map("LineExtensionTaxAmount")
	 */
	private string $lineExtensionTaxAmount;

	/**
	 * Unit price without tax in a local currency.
	 *
	 * @Map("UnitPrice")
	 */
	private string $unitPrice;

	/**
	 * Unit price including tax in a local currency.
	 *
	 * @Map("UnitPriceTaxInclusive")
	 */
	private string $unitPriceTaxInclusive;

	/**
	 * Compound VAT field.
	 *
	 * @Map("ClassifiedTaxCategory")
	 */
	private ClassifiedTaxCategory $classifiedTaxCategory;

	/**
	 * Note.
	 *
	 * @Map("Note")
	 */
	private ?Note $note = null;

	/**
	 * Legislation citation which defines VAT exception for this line.
	 *
	 * @Map("VATNote")
	 */
	private ?Note $vatNote = null;

	/**
	 * Information directly relating to an item.
	 *
	 * @Map("Item")
	 */
	private ?Item $item = null;

	public function __construct(
		string $id,
		string $lineExtensionAmount,
		string $lineExtensionAmountTaxInclusive,
		string $lineExtensionTaxAmount,
		string $unitPrice,
		string $unitPriceTaxInclusive,
		ClassifiedTaxCategory $classifiedTaxCategory
	) {
		$this->setId($id);
		$this->setLineExtensionAmount($lineExtensionAmount);
		$this->setLineExtensionAmountTaxInclusive($lineExtensionAmountTaxInclusive);
		$this->setLineExtensionTaxAmount($lineExtensionTaxAmount);
		$this->setUnitPrice($unitPrice);
		$this->setUnitPriceTaxInclusive($unitPriceTaxInclusive);
		$this->setClassifiedTaxCategory($classifiedTaxCategory);
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId(string $id): self
	{
		Restriction::maxLength($id, 36);
		$this->id = $id;
		return $this;
	}

	public function getOrder(): ?OrderLine
	{
		return $this->order;
	}

	public function setOrder(?OrderLine $order): self
	{
		$this->order = $order;
		return $this;
	}

	public function getDeliveryNote(): ?DeliveryNoteLine
	{
		return $this->deliveryNote;
	}

	public function setDeliveryNote(?DeliveryNoteLine $deliveryNote): self
	{
		$this->deliveryNote = $deliveryNote;
		return $this;
	}

	public function getOriginalDocument(): ?OriginalDocumentLine
	{
		return $this->originalDocument;
	}

	public function setOriginalDocument(?OriginalDocumentLine $originalDocument): self
	{
		$this->originalDocument = $originalDocument;
		return $this;
	}

	public function getContract(): ?ContractLine
	{
		return $this->contract;
	}

	public function setContract(?ContractLine $contract): self
	{
		$this->contract = $contract;
		return $this;
	}

	public function getEgovClassifier(): ?string
	{
		return $this->egovClassifier;
	}

	public function setEgovClassifier(?string $egovClassifier): self
	{
		$this->egovClassifier = $egovClassifier;
		return $this;
	}

	public function getInvoicedQuantity(): ?Quantity
	{
		return $this->invoicedQuantity;
	}

	public function setInvoicedQuantity(?Quantity $invoicedQuantity): self
	{
		$this->invoicedQuantity = $invoicedQuantity;
		return $this;
	}

	public function getLineExtensionAmountCurr(): ?string
	{
		return $this->lineExtensionAmountCurr;
	}

	public function setLineExtensionAmountCurr(?string $lineExtensionAmountCurr): self
	{
		Restriction::decimal($lineExtensionAmountCurr);
		$this->lineExtensionAmountCurr = $lineExtensionAmountCurr;
		return $this;
	}

	public function getLineExtensionAmount(): string
	{
		return $this->lineExtensionAmount;
	}

	public function setLineExtensionAmount(string $lineExtensionAmount): self
	{
		Restriction::decimal($lineExtensionAmount);
		$this->lineExtensionAmount = $lineExtensionAmount;
		return $this;
	}

	public function getLineExtensionAmountBeforeDiscount(): ?string
	{
		return $this->lineExtensionAmountBeforeDiscount;
	}

	public function setLineExtensionAmountBeforeDiscount(?string $lineExtensionAmountBeforeDiscount): self
	{
		Restriction::decimal($lineExtensionAmountBeforeDiscount);
		$this->lineExtensionAmountBeforeDiscount = $lineExtensionAmountBeforeDiscount;
		return $this;
	}

	public function getLineExtensionAmountTaxInclusiveCurr(): ?string
	{
		return $this->lineExtensionAmountTaxInclusiveCurr;
	}

	public function setLineExtensionAmountTaxInclusiveCurr(?string $lineExtensionAmountTaxInclusiveCurr): self
	{
		Restriction::decimal($lineExtensionAmountTaxInclusiveCurr);
		$this->lineExtensionAmountTaxInclusiveCurr = $lineExtensionAmountTaxInclusiveCurr;
		return $this;
	}

	public function getLineExtensionAmountTaxInclusive(): string
	{
		return $this->lineExtensionAmountTaxInclusive;
	}

	public function setLineExtensionAmountTaxInclusive(string $lineExtensionAmountTaxInclusive): self
	{
		Restriction::decimal($lineExtensionAmountTaxInclusive);
		$this->lineExtensionAmountTaxInclusive = $lineExtensionAmountTaxInclusive;
		return $this;
	}

	public function getLineExtensionAmountTaxInclusiveBeforeDiscount(): ?string
	{
		return $this->lineExtensionAmountTaxInclusiveBeforeDiscount;
	}

	public function setLineExtensionAmountTaxInclusiveBeforeDiscount(?string $lineExtensionAmountTaxInclusiveBeforeDiscount): self
	{
		Restriction::decimal($lineExtensionAmountTaxInclusiveBeforeDiscount);
		$this->lineExtensionAmountTaxInclusiveBeforeDiscount = $lineExtensionAmountTaxInclusiveBeforeDiscount;
		return $this;
	}

	public function getLineExtensionTaxAmount(): string
	{
		return $this->lineExtensionTaxAmount;
	}

	public function setLineExtensionTaxAmount(string $lineExtensionTaxAmount): self
	{
		Restriction::decimal($lineExtensionTaxAmount);
		$this->lineExtensionTaxAmount = $lineExtensionTaxAmount;
		return $this;
	}

	public function getUnitPrice(): string
	{
		return $this->unitPrice;
	}

	public function setUnitPrice(string $unitPrice): self
	{
		Restriction::decimal($unitPrice);
		$this->unitPrice = $unitPrice;
		return $this;
	}

	public function getUnitPriceTaxInclusive(): string
	{
		return $this->unitPriceTaxInclusive;
	}

	public function setUnitPriceTaxInclusive(string $unitPriceTaxInclusive): self
	{
		Restriction::decimal($unitPriceTaxInclusive);
		$this->unitPriceTaxInclusive = $unitPriceTaxInclusive;
		return $this;
	}

	public function getClassifiedTaxCategory(): ClassifiedTaxCategory
	{
		return $this->classifiedTaxCategory;
	}

	public function setClassifiedTaxCategory(ClassifiedTaxCategory $classifiedTaxCategory): self
	{
		$this->classifiedTaxCategory = $classifiedTaxCategory;
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

	public function getVatNote(): ?Note
	{
		return $this->vatNote;
	}

	public function setVatNote(?Note $vatNote): self
	{
		$this->vatNote = $vatNote;
		return $this;
	}

	public function getItem(): ?Item
	{
		return $this->item;
	}

	public function setItem(?Item $item): self
	{
		$this->item = $item;
		return $this;
	}

}