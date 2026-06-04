<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Deprecations;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use BcMath;
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
 * @property string|BcMath\Number|null $lineExtensionAmountCurr
 * @property string|BcMath\Number      $lineExtensionAmount
 * @property string|BcMath\Number|null $lineExtensionAmountBeforeDiscount
 * @property string|BcMath\Number|null $lineExtensionAmountTaxInclusiveCurr
 * @property string|BcMath\Number      $lineExtensionAmountTaxInclusive
 * @property string|BcMath\Number|null $lineExtensionAmountTaxInclusiveBeforeDiscount
 * @property string|BcMath\Number      $lineExtensionTaxAmount
 * @property string|BcMath\Number      $unitPrice
 * @property string|BcMath\Number      $unitPriceTaxInclusive
 * @property ClassifiedTaxCategory     $classifiedTaxCategory
 * @property Note|null                 $note
 * @property Note|null                 $vatNote
 * @property Item|null                 $item
 */
class InvoiceLine implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Unique alphanumeric line identifier. */
	#[Map('ID')]
	private string $id;

	/** Reference to line on a related purchase order. */
	#[Map('OrderReference')]
	private ?OrderLine $order = null;

	/** Information about referenced line on delivery note. */
	#[Map('DeliveryNoteReference')]
	private ?DeliveryNoteLine $deliveryNote = null;

	/** Line reference to an original document which is being corrected by this document (only for document types 2, 3 and 6). */
	#[Map('OriginalDocumentReference')]
	private ?OriginalDocumentLine $originalDocument = null;

	/** Reference to a related contract. */
	#[Map('ContractReference')]
	private ?ContractLine $contract = null;

	/** Egoverment accounting classifier. */
	#[Map('EgovClassifier')]
	private ?string $egovClassifier = null;

	/** Invoiced quantity. */
	#[Map('InvoicedQuantity')]
	private ?Quantity $invoicedQuantity = null;

	/** Total line amount without tax in a foreign currency. */
	#[Map('LineExtensionAmountCurr')]
	private ?string $lineExtensionAmountCurr = null;

	/**
	 * Total line amount without tax in a local currency.
	 * @var numeric-string
	 */
	#[Map('LineExtensionAmount')]
	private string $lineExtensionAmount;

	/** Total line amount without tax in a local currency without discount. */
	#[Map('LineExtensionAmountBeforeDiscount')]
	private ?string $lineExtensionAmountBeforeDiscount = null;

	/** Total line amount including tax in a foreign currency. */
	#[Map('LineExtensionAmountTaxInclusiveCurr')]
	private ?string $lineExtensionAmountTaxInclusiveCurr = null;

	/**
	 * Total line amount including tax in a local currency.
	 * @var numeric-string
	 */
	#[Map('LineExtensionAmountTaxInclusive')]
	private string $lineExtensionAmountTaxInclusive;

	/** Total line amount including tax in a local currency without discount. */
	#[Map('LineExtensionAmountTaxInclusiveBeforeDiscount')]
	private ?string $lineExtensionAmountTaxInclusiveBeforeDiscount = null;

	/** Line tax amount in a local currency. */
	#[Map('LineExtensionTaxAmount')]
	private string $lineExtensionTaxAmount;

	/** Unit price without tax in a local currency. */
	#[Map('UnitPrice')]
	private string $unitPrice;

	/** Unit price including tax in a local currency. */
	#[Map('UnitPriceTaxInclusive')]
	private string $unitPriceTaxInclusive;

	/** Compound VAT field. */
	#[Map('ClassifiedTaxCategory')]
	private ClassifiedTaxCategory $classifiedTaxCategory;

	/** Note. */
	#[Map('Note')]
	private ?Note $note = null;

	/** Legislation citation which defines VAT exception for this line. */
	#[Map('VATNote')]
	private ?Note $vatNote = null;

	/** Information directly relating to an item. */
	#[Map('Item')]
	private ?Item $item = null;

	/**
	 * @param numeric-string|BcMath\Number $lineExtensionAmount
	 * @param numeric-string|BcMath\Number $lineExtensionAmountTaxInclusive
	 */
	public function __construct(
		string                $id,
		string|BcMath\Number  $lineExtensionAmount,
		string|BcMath\Number  $lineExtensionAmountTaxInclusive,
		string|BcMath\Number  $lineExtensionTaxAmount,
		string|BcMath\Number  $unitPrice,
		string|BcMath\Number  $unitPriceTaxInclusive,
		ClassifiedTaxCategory $classifiedTaxCategory,
	)
	{
		$this->setId($id);
		$this->setLineExtensionAmount($lineExtensionAmount);
		$this->setLineExtensionAmountTaxInclusive($lineExtensionAmountTaxInclusive);
		$this->setLineExtensionTaxAmount($lineExtensionTaxAmount);
		$this->setUnitPrice($unitPrice);
		$this->setUnitPriceTaxInclusive($unitPriceTaxInclusive);
		$this->setClassifiedTaxCategory($classifiedTaxCategory);
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function getId(): string
	{
		return $this->id;
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function setId(string $id): self
	{
		Restriction::maxLength($id, 36);
		$this->id = $id;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $order} property instead. */
	public function getOrder(): ?OrderLine
	{
		return $this->order;
	}

	/** @deprecated Method accessors are deprecated, use {@see $order} property instead. */
	public function setOrder(?OrderLine $order): self
	{
		$this->order = $order;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $deliveryNote} property instead. */
	public function getDeliveryNote(): ?DeliveryNoteLine
	{
		return $this->deliveryNote;
	}

	/** @deprecated Method accessors are deprecated, use {@see $deliveryNote} property instead. */
	public function setDeliveryNote(?DeliveryNoteLine $deliveryNote): self
	{
		$this->deliveryNote = $deliveryNote;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $originalDocument} property instead. */
	public function getOriginalDocument(): ?OriginalDocumentLine
	{
		return $this->originalDocument;
	}

	/** @deprecated Method accessors are deprecated, use {@see $originalDocument} property instead. */
	public function setOriginalDocument(?OriginalDocumentLine $originalDocument): self
	{
		$this->originalDocument = $originalDocument;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $contract} property instead. */
	public function getContract(): ?ContractLine
	{
		return $this->contract;
	}

	/** @deprecated Method accessors are deprecated, use {@see $contract} property instead. */
	public function setContract(?ContractLine $contract): self
	{
		$this->contract = $contract;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $egovClassifier} property instead. */
	public function getEgovClassifier(): ?string
	{
		return $this->egovClassifier;
	}

	/** @deprecated Method accessors are deprecated, use {@see $egovClassifier} property instead. */
	public function setEgovClassifier(?string $egovClassifier): self
	{
		$this->egovClassifier = $egovClassifier;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $invoicedQuantity} property instead. */
	public function getInvoicedQuantity(): ?Quantity
	{
		return $this->invoicedQuantity;
	}

	/** @deprecated Method accessors are deprecated, use {@see $invoicedQuantity} property instead. */
	public function setInvoicedQuantity(?Quantity $invoicedQuantity): self
	{
		$this->invoicedQuantity = $invoicedQuantity;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountCurr} property instead. */
	public function getLineExtensionAmountCurr(): ?string
	{
		return $this->lineExtensionAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountCurr} property instead. */
	public function setLineExtensionAmountCurr(string|BcMath\Number|null $lineExtensionAmountCurr): self
	{
		Deprecations::number($lineExtensionAmountCurr);
		$lineExtensionAmountCurr = $lineExtensionAmountCurr === null ? null : (string) $lineExtensionAmountCurr;
		Restriction::decimal($lineExtensionAmountCurr);
		$this->lineExtensionAmountCurr = $lineExtensionAmountCurr;
		return $this;
	}

	/**
	 * @deprecated Method accessors are deprecated, use {@see $lineExtensionAmount} property instead.
	 * @return numeric-string
	 */
	public function getLineExtensionAmount(): string
	{
		return $this->lineExtensionAmount;
	}

	/**
	 * @deprecated Method accessors are deprecated, use {@see $lineExtensionAmount} property instead.
	 * @param numeric-string|BcMath\Number $lineExtensionAmount
	 */
	public function setLineExtensionAmount(string|BcMath\Number $lineExtensionAmount): self
	{
		Deprecations::number($lineExtensionAmount);
		$lineExtensionAmount = (string) $lineExtensionAmount;
		Restriction::decimal($lineExtensionAmount);
		$this->lineExtensionAmount = $lineExtensionAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountBeforeDiscount} property instead. */
	public function getLineExtensionAmountBeforeDiscount(): ?string
	{
		return $this->lineExtensionAmountBeforeDiscount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountBeforeDiscount} property instead. */
	public function setLineExtensionAmountBeforeDiscount(
		string|BcMath\Number|null $lineExtensionAmountBeforeDiscount,
	): self
	{
		Deprecations::number($lineExtensionAmountBeforeDiscount);
		$lineExtensionAmountBeforeDiscount = $lineExtensionAmountBeforeDiscount === null ? null : (string) $lineExtensionAmountBeforeDiscount;
		Restriction::decimal($lineExtensionAmountBeforeDiscount);
		$this->lineExtensionAmountBeforeDiscount = $lineExtensionAmountBeforeDiscount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountTaxInclusiveCurr} property instead. */
	public function getLineExtensionAmountTaxInclusiveCurr(): ?string
	{
		return $this->lineExtensionAmountTaxInclusiveCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountTaxInclusiveCurr} property instead. */
	public function setLineExtensionAmountTaxInclusiveCurr(
		string|BcMath\Number|null $lineExtensionAmountTaxInclusiveCurr,
	): self
	{
		Deprecations::number($lineExtensionAmountTaxInclusiveCurr);
		$lineExtensionAmountTaxInclusiveCurr = $lineExtensionAmountTaxInclusiveCurr === null ? null : (string) $lineExtensionAmountTaxInclusiveCurr;
		Restriction::decimal($lineExtensionAmountTaxInclusiveCurr);
		$this->lineExtensionAmountTaxInclusiveCurr = $lineExtensionAmountTaxInclusiveCurr;
		return $this;
	}

	/**
	 * @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountTaxInclusive} property instead.
	 * @return numeric-string
	 */
	public function getLineExtensionAmountTaxInclusive(): string
	{
		return $this->lineExtensionAmountTaxInclusive;
	}

	/**
	 * @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountTaxInclusive} property instead.
	 * @param numeric-string|BcMath\Number $lineExtensionAmountTaxInclusive
	 */
	public function setLineExtensionAmountTaxInclusive(string|BcMath\Number $lineExtensionAmountTaxInclusive): self
	{
		Deprecations::number($lineExtensionAmountTaxInclusive);
		$lineExtensionAmountTaxInclusive = (string) $lineExtensionAmountTaxInclusive;
		Restriction::decimal($lineExtensionAmountTaxInclusive);
		$this->lineExtensionAmountTaxInclusive = $lineExtensionAmountTaxInclusive;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountTaxInclusiveBeforeDiscount} property instead. */
	public function getLineExtensionAmountTaxInclusiveBeforeDiscount(): ?string
	{
		return $this->lineExtensionAmountTaxInclusiveBeforeDiscount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionAmountTaxInclusiveBeforeDiscount} property instead. */
	public function setLineExtensionAmountTaxInclusiveBeforeDiscount(
		string|BcMath\Number|null $lineExtensionAmountTaxInclusiveBeforeDiscount,
	): self
	{
		Deprecations::number($lineExtensionAmountTaxInclusiveBeforeDiscount);
		$lineExtensionAmountTaxInclusiveBeforeDiscount = $lineExtensionAmountTaxInclusiveBeforeDiscount === null ? null : (string) $lineExtensionAmountTaxInclusiveBeforeDiscount;
		Restriction::decimal($lineExtensionAmountTaxInclusiveBeforeDiscount);
		$this->lineExtensionAmountTaxInclusiveBeforeDiscount = $lineExtensionAmountTaxInclusiveBeforeDiscount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionTaxAmount} property instead. */
	public function getLineExtensionTaxAmount(): string
	{
		return $this->lineExtensionTaxAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $lineExtensionTaxAmount} property instead. */
	public function setLineExtensionTaxAmount(string|BcMath\Number $lineExtensionTaxAmount): self
	{
		Deprecations::number($lineExtensionTaxAmount);
		$lineExtensionTaxAmount = (string) $lineExtensionTaxAmount;
		Restriction::decimal($lineExtensionTaxAmount);
		$this->lineExtensionTaxAmount = $lineExtensionTaxAmount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $unitPrice} property instead. */
	public function getUnitPrice(): string
	{
		return $this->unitPrice;
	}

	/** @deprecated Method accessors are deprecated, use {@see $unitPrice} property instead. */
	public function setUnitPrice(string|BcMath\Number $unitPrice): self
	{
		Deprecations::number($unitPrice);
		$unitPrice = (string) $unitPrice;
		Restriction::decimal($unitPrice);
		$this->unitPrice = $unitPrice;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $unitPriceTaxInclusive} property instead. */
	public function getUnitPriceTaxInclusive(): string
	{
		return $this->unitPriceTaxInclusive;
	}

	/** @deprecated Method accessors are deprecated, use {@see $unitPriceTaxInclusive} property instead. */
	public function setUnitPriceTaxInclusive(string|BcMath\Number $unitPriceTaxInclusive): self
	{
		Deprecations::number($unitPriceTaxInclusive);
		$unitPriceTaxInclusive = (string) $unitPriceTaxInclusive;
		Restriction::decimal($unitPriceTaxInclusive);
		$this->unitPriceTaxInclusive = $unitPriceTaxInclusive;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $classifiedTaxCategory} property instead. */
	public function getClassifiedTaxCategory(): ClassifiedTaxCategory
	{
		return $this->classifiedTaxCategory;
	}

	/** @deprecated Method accessors are deprecated, use {@see $classifiedTaxCategory} property instead. */
	public function setClassifiedTaxCategory(ClassifiedTaxCategory $classifiedTaxCategory): self
	{
		$this->classifiedTaxCategory = $classifiedTaxCategory;
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

	/** @deprecated Method accessors are deprecated, use {@see $vatNote} property instead. */
	public function getVatNote(): ?Note
	{
		return $this->vatNote;
	}

	/** @deprecated Method accessors are deprecated, use {@see $vatNote} property instead. */
	public function setVatNote(?Note $vatNote): self
	{
		$this->vatNote = $vatNote;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $item} property instead. */
	public function getItem(): ?Item
	{
		return $this->item;
	}

	/** @deprecated Method accessors are deprecated, use {@see $item} property instead. */
	public function setItem(?Item $item): self
	{
		$this->item = $item;
		return $this;
	}

}