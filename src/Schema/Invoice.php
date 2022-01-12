<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use DateTimeInterface;
use Nette\SmartObject;

/**
 * Document root element, subtype is stored in DocumentType element.
 *
 * @property int                                     $documentType
 * @property string|null                             $subDocumentType
 * @property string|null                             $subDocumentTypeOrigin
 * @property string|null                             $targetConsolidator
 * @property string|null                             $clientOnTargetConsolidator
 * @property string|null                             $clientBankAccount
 * @property string                                  $id
 * @property string                                  $uuid
 * @property bool|null                               $egovFlag
 * @property string|null                             $isds_id
 * @property string|null                             $file
 * @property string|null                             $referenceNumber
 * @property Invoice\EgovClassifiers|null            $egovClassifiers
 * @property string|null                             $issuingSystem
 * @property DateTimeInterface                       $issueDate
 * @property DateTimeInterface|null                  $taxPointDate
 * @property bool                                    $vatApplicable
 * @property Invoice\Note                            $electronicPossibilityAgreement
 * @property Invoice\Note|null                       $note
 * @property string                                  $localCurrencyCode
 * @property string|null                             $foreignCurrencyCode
 * @property string                                  $currRate
 * @property string                                  $refCurrRate
 * @property Invoice\AccountingSupplierParty         $accountingSupplierParty
 * @property Invoice\SellerSupplierParty|null        $sellerSupplierParty
 * @property Invoice\AccountingCustomerParty|null    $accountingCustomerParty
 * @property Invoice\AnonymousCustomerParty|null     $anonymousCustomerParty
 * @property Invoice\BuyerCustomerParty|null         $buyerCustomerParty
 * @property Invoice\OrderReferences|null            $orderReferences
 * @property Invoice\DeliveryNoteReferences|null     $deliveryNoteReferences
 * @property Invoice\OriginalDocumentReferences|null $originalDocumentReferences
 * @property Invoice\ContractReferences|null         $contractReferences
 * @property Invoice\Delivery|null                   $delivery
 * @property Invoice\InvoiceLines                    $invoiceLines
 * @property Invoice\NonTaxedDeposits|null           $nonTaxedDeposits
 * @property Invoice\TaxedDeposits|null              $taxedDeposits
 * @property Invoice\TaxTotal                        $taxTotal
 * @property Invoice\LegalMonetaryTotal              $legalMonetaryTotal
 * @property Invoice\PaymentMeans|null               $paymentMeans
 * @property Invoice\SupplementsList|null            $supplementsList
 * @property string                                  $version
 */
class Invoice implements Arrayable
{

	use SmartObject;
	use ToArray;

	public const DOCUMENT_TYPE_INVOICE = 1;
	public const DOCUMENT_TYPE_CREDIT_NOTE = 2;
	public const DOCUMENT_TYPE_DEBIT_NOTE = 3;
	public const DOCUMENT_TYPE_PROFORMA_INVOICE_NO_VAT = 4;
	public const DOCUMENT_TYPE_ADVANCE_INVOICE_WITH_VAT = 5;
	public const DOCUMENT_TYPE_CREDIT_NOTE_FOR_ADVANCE_INVOICE_WITH_VAT = 6;
	public const DOCUMENT_TYPE_SIMPLIFIED_TAX_DOCUMENT = 7;

	/**
	 * Document type.
	 *
	 * @Map("DocumentType")
	 */
	private int $documentType;

	/**
	 * Document subtype. Codelist is maintained and published by subject specified in SubDocumentTypeOrigin.
	 *
	 * @Map("SubDocumentType")
	 */
	private ?string $subDocumentType = null;

	/**
	 * Maintainer of subdocument type code list.
	 *
	 * @Map("SubDocumentTypeOrigin")
	 */
	private ?string $subDocumentTypeOrigin = null;

	/**
	 * Identification of target consolidator. Values are extended list of bank codes maintained and published by ČBA.
	 *
	 * @Map("TargetConsolidator")
	 */
	private ?string $targetConsolidator = null;

	/**
	 * Client identification in the issuer system. Used mainly in B2C systems of companies issuing large volume of invoices.
	 *
	 * @Map("ClientOnTargetConsolidator")
	 */
	private ?string $clientOnTargetConsolidator = null;

	/**
	 * Complete bank account number of invoice receiver. Used mainly in B2C systems of companies issuing large volume of invoices.
	 *
	 * @Map("ClientBankAccount")
	 */
	private ?string $clientBankAccount = null;

	/**
	 * Human readable document number.
	 *
	 * @Map("ID")
	 */
	private string $id;

	/**
	 * GUID identifier produced by emitting system.
	 *
	 * @Map("UUID")
	 */
	private string $uuid;

	/**
	 * Flag for state governed documents.
	 *
	 * @Map("EgovFlag")
	 */
	private ?bool $egovFlag = null;

	/**
	 * Unique identifier inside ISDS system.
	 *
	 * @Map("ISDS_ID")
	 */
	private ?string $isds_id = null;

	/**
	 * File number.
	 *
	 * @Map("FileReference")
	 */
	private ?string $file = null;

	/**
	 * Reference number.
	 *
	 * @Map("ReferenceNumber")
	 */
	private ?string $referenceNumber = null;

	/**
	 * Collection of classifiers.
	 *
	 * @Map("EgovClassifiers")
	 */
	private ?Invoice\EgovClassifiers $egovClassifiers = null;

	/**
	 * Identification of issuing system.
	 *
	 * @Map("IssuingSystem")
	 */
	private ?string $issuingSystem = null;

	/**
	 * Issue date.
	 *
	 * @Map("IssueDate")
	 */
	private DateTimeInterface $issueDate;

	/**
	 * Tax point date.
	 *
	 * @Map("TaxPointDate")
	 */
	private ?DateTimeInterface $taxPointDate = null;

	/**
	 * VAT is applicable.
	 *
	 * @Map("VATApplicable")
	 */
	private bool $vatApplicable;

	/**
	 * Reference to agreement about acceptance of electronic invoices.
	 *
	 * @Map("ElectronicPossibilityAgreementReference")
	 */
	private Invoice\Note $electronicPossibilityAgreement;

	/**
	 * Note.
	 *
	 * @Map("Note")
	 */
	private ?Invoice\Note $note = null;

	/**
	 * Currency code.
	 *
	 * @Map("LocalCurrencyCode")
	 */
	private string $localCurrencyCode;

	/**
	 * Currency code.
	 *
	 * @Map("ForeignCurrencyCode")
	 */
	private ?string $foreignCurrencyCode = null;

	/**
	 * Foreign currency exchange rate (if foreign currency is used), otherwise 1.
	 *
	 * @Map("CurrRate")
	 */
	private string $currRate;

	/**
	 * Reference foreign currency exchange rate, usually 1.
	 *
	 * @Map("RefCurrRate")
	 */
	private string $refCurrRate;

	/**
	 * Supplier, accounting entity in Commercial Register.
	 *
	 * @Map("AccountingSupplierParty")
	 */
	private Invoice\AccountingSupplierParty $accountingSupplierParty;

	/**
	 * Supplier, invoicing address.
	 *
	 * @Map("SellerSupplierParty")
	 */
	private ?Invoice\SellerSupplierParty $sellerSupplierParty = null;

	/**
	 * Customer, accounting entity in Commercial Register.
	 *
	 * @Map("AccountingCustomerParty")
	 */
	private ?Invoice\AccountingCustomerParty $accountingCustomerParty = null;

	/**
	 * Anonymous receiver of simplified tax document.
	 *
	 * @Map("AnonymousCustomerParty")
	 */
	private ?Invoice\AnonymousCustomerParty $anonymousCustomerParty = null;

	/**
	 * Purchaser, invoicing address.
	 *
	 * @Map("BuyerCustomerParty")
	 */
	private ?Invoice\BuyerCustomerParty $buyerCustomerParty = null;

	/**
	 * Header collection of referenced purchase order(s).
	 *
	 * @Map("OrderReferences")
	 */
	private ?Invoice\OrderReferences $orderReferences = null;

	/**
	 * Header collection of referenced delivery notes.
	 *
	 * @Map("DeliveryNoteReferences")
	 */
	private ?Invoice\DeliveryNoteReferences $deliveryNoteReferences = null;

	/**
	 * Header collection of referenced original documents.
	 *
	 * @Map("OriginalDocumentReferences")
	 */
	private ?Invoice\OriginalDocumentReferences $originalDocumentReferences = null;

	/**
	 * Related contracts.
	 *
	 * @Map("ContractReferences")
	 */
	private ?Invoice\ContractReferences $contractReferences = null;

	/**
	 * Information about delivery.
	 *
	 * @Map("Delivery")
	 */
	private ?Invoice\Delivery $delivery = null;

	/**
	 * Invoice lines collection.
	 *
	 * @Map("InvoiceLines")
	 */
	private Invoice\InvoiceLines $invoiceLines;

	/**
	 * Collection of proforma invoices (without VAT).
	 *
	 * @Map("NonTaxedDeposits")
	 */
	private ?Invoice\NonTaxedDeposits $nonTaxedDeposits = null;

	/**
	 * Collection of taxed deposits (advance invoices with VAT).
	 *
	 * @Map("TaxedDeposits")
	 */
	private ?Invoice\TaxedDeposits $taxedDeposits = null;

	/**
	 * Information about a total amount of a particular type of tax.
	 *
	 * @Map("TaxTotal")
	 */
	private Invoice\TaxTotal $taxTotal;

	/**
	 * Collection of total amounts on document ending with payable amount.
	 *
	 * @Map("LegalMonetaryTotal")
	 */
	private Invoice\LegalMonetaryTotal $legalMonetaryTotal;

	/**
	 * Information about payment means.
	 *
	 * @Map("PaymentMeans")
	 */
	private ?Invoice\PaymentMeans $paymentMeans = null;

	/**
	 * Collection of document attachments. Exactly one attachment can be document preview marked by preview="true".
	 *
	 * @Map("SupplementsList")
	 */
	private ?Invoice\SupplementsList $supplementsList = null;

	/**
	 * ISDOC version number.
	 *
	 * @Map("@version")
	 */
	private string $version;

	public function __construct(
		int $documentType,
		string $id,
		string $uuid,
		DateTimeInterface $issueDate,
		bool $vatApplicable,
		Invoice\Note $electronicPossibilityAgreement,
		string $localCurrencyCode,
		string $currRate,
		string $refCurrRate,
		Invoice\AccountingSupplierParty $accountingSupplierParty,
		Invoice\InvoiceLines $invoiceLines,
		Invoice\TaxTotal $taxTotal,
		Invoice\LegalMonetaryTotal $legalMonetaryTotal,
		string $version
	) {
		$this->setDocumentType($documentType);
		$this->setId($id);
		$this->setUuid($uuid);
		$this->setIssueDate($issueDate);
		$this->setVatApplicable($vatApplicable);
		$this->setElectronicPossibilityAgreement($electronicPossibilityAgreement);
		$this->setLocalCurrencyCode($localCurrencyCode);
		$this->setCurrRate($currRate);
		$this->setRefCurrRate($refCurrRate);
		$this->setAccountingSupplierParty($accountingSupplierParty);
		$this->setInvoiceLines($invoiceLines);
		$this->setTaxTotal($taxTotal);
		$this->setLegalMonetaryTotal($legalMonetaryTotal);
		$this->setVersion($version);
	}

	public function getDocumentType(): int
	{
		return $this->documentType;
	}

	public function setDocumentType(int $documentType): self
	{
		Restriction::enumeration($documentType, [
			self::DOCUMENT_TYPE_INVOICE,
			self::DOCUMENT_TYPE_CREDIT_NOTE,
			self::DOCUMENT_TYPE_DEBIT_NOTE,
			self::DOCUMENT_TYPE_PROFORMA_INVOICE_NO_VAT,
			self::DOCUMENT_TYPE_ADVANCE_INVOICE_WITH_VAT,
			self::DOCUMENT_TYPE_CREDIT_NOTE_FOR_ADVANCE_INVOICE_WITH_VAT,
			self::DOCUMENT_TYPE_SIMPLIFIED_TAX_DOCUMENT,
		]);
		$this->documentType = $documentType;
		return $this;
	}

	public function getSubDocumentType(): ?string
	{
		return $this->subDocumentType;
	}

	public function setSubDocumentType(?string $subDocumentType): self
	{
		$this->subDocumentType = $subDocumentType;
		return $this;
	}

	public function getSubDocumentTypeOrigin(): ?string
	{
		return $this->subDocumentTypeOrigin;
	}

	public function setSubDocumentTypeOrigin(?string $subDocumentTypeOrigin): self
	{
		$this->subDocumentTypeOrigin = $subDocumentTypeOrigin;
		return $this;
	}

	public function getTargetConsolidator(): ?string
	{
		return $this->targetConsolidator;
	}

	public function setTargetConsolidator(?string $targetConsolidator): self
	{
		$this->targetConsolidator = $targetConsolidator;
		return $this;
	}

	public function getClientOnTargetConsolidator(): ?string
	{
		return $this->clientOnTargetConsolidator;
	}

	public function setClientOnTargetConsolidator(?string $clientOnTargetConsolidator): self
	{
		$this->clientOnTargetConsolidator = $clientOnTargetConsolidator;
		return $this;
	}

	public function getClientBankAccount(): ?string
	{
		return $this->clientBankAccount;
	}

	public function setClientBankAccount(?string $clientBankAccount): self
	{
		$this->clientBankAccount = $clientBankAccount;
		return $this;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	public function getUuid(): string
	{
		return $this->uuid;
	}

	public function setUuid(string $uuid): self
	{
		Restriction::pattern($uuid, '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}');
		$this->uuid = $uuid;
		return $this;
	}

	public function getEgovFlag(): ?bool
	{
		return $this->egovFlag;
	}

	public function setEgovFlag(?bool $egovFlag): self
	{
		$this->egovFlag = $egovFlag;
		return $this;
	}

	public function getIsds_id(): ?string
	{
		return $this->isds_id;
	}

	public function setIsds_id(?string $isds_id): self
	{
		$this->isds_id = $isds_id;
		return $this;
	}

	public function getFile(): ?string
	{
		return $this->file;
	}

	public function setFile(?string $file): self
	{
		$this->file = $file;
		return $this;
	}

	public function getReferenceNumber(): ?string
	{
		return $this->referenceNumber;
	}

	public function setReferenceNumber(?string $referenceNumber): self
	{
		$this->referenceNumber = $referenceNumber;
		return $this;
	}

	public function getEgovClassifiers(): ?Invoice\EgovClassifiers
	{
		return $this->egovClassifiers;
	}

	public function setEgovClassifiers(?Invoice\EgovClassifiers $egovClassifiers): self
	{
		$this->egovClassifiers = $egovClassifiers;
		return $this;
	}

	public function getIssuingSystem(): ?string
	{
		return $this->issuingSystem;
	}

	public function setIssuingSystem(?string $issuingSystem): self
	{
		Restriction::maxLength($issuingSystem, 80);
		$this->issuingSystem = $issuingSystem;
		return $this;
	}

	public function getIssueDate(): DateTimeInterface
	{
		return $this->issueDate;
	}

	public function setIssueDate(DateTimeInterface $issueDate): self
	{
		$this->issueDate = $issueDate;
		return $this;
	}

	public function getTaxPointDate(): ?DateTimeInterface
	{
		return $this->taxPointDate;
	}

	public function setTaxPointDate(?DateTimeInterface $taxPointDate): self
	{
		$this->taxPointDate = $taxPointDate;
		return $this;
	}

	public function getVatApplicable(): bool
	{
		return $this->vatApplicable;
	}

	public function setVatApplicable(bool $vatApplicable): self
	{
		$this->vatApplicable = $vatApplicable;
		return $this;
	}

	public function getElectronicPossibilityAgreement(): Invoice\Note
	{
		return $this->electronicPossibilityAgreement;
	}

	public function setElectronicPossibilityAgreement(Invoice\Note $electronicPossibilityAgreement): self
	{
		$this->electronicPossibilityAgreement = $electronicPossibilityAgreement;
		return $this;
	}

	public function getNote(): ?Invoice\Note
	{
		return $this->note;
	}

	public function setNote(?Invoice\Note $note): self
	{
		$this->note = $note;
		return $this;
	}

	public function getLocalCurrencyCode(): string
	{
		return $this->localCurrencyCode;
	}

	public function setLocalCurrencyCode(string $localCurrencyCode): self
	{
		Restriction::length($localCurrencyCode, 3);
		$this->localCurrencyCode = $localCurrencyCode;
		return $this;
	}

	public function getForeignCurrencyCode(): ?string
	{
		return $this->foreignCurrencyCode;
	}

	public function setForeignCurrencyCode(?string $foreignCurrencyCode): self
	{
		Restriction::length($foreignCurrencyCode, 3);
		$this->foreignCurrencyCode = $foreignCurrencyCode;
		return $this;
	}

	public function getCurrRate(): string
	{
		return $this->currRate;
	}

	public function setCurrRate(string $currRate): self
	{
		Restriction::decimal($currRate);
		$this->currRate = $currRate;
		return $this;
	}

	public function getRefCurrRate(): string
	{
		return $this->refCurrRate;
	}

	public function setRefCurrRate(string $refCurrRate): self
	{
		Restriction::decimal($refCurrRate);
		$this->refCurrRate = $refCurrRate;
		return $this;
	}

	public function getAccountingSupplierParty(): Invoice\AccountingSupplierParty
	{
		return $this->accountingSupplierParty;
	}

	public function setAccountingSupplierParty(Invoice\AccountingSupplierParty $accountingSupplierParty): self
	{
		$this->accountingSupplierParty = $accountingSupplierParty;
		return $this;
	}

	public function getSellerSupplierParty(): ?Invoice\SellerSupplierParty
	{
		return $this->sellerSupplierParty;
	}

	public function setSellerSupplierParty(?Invoice\SellerSupplierParty $sellerSupplierParty): self
	{
		$this->sellerSupplierParty = $sellerSupplierParty;
		return $this;
	}

	public function getAccountingCustomerParty(): ?Invoice\AccountingCustomerParty
	{
		return $this->accountingCustomerParty;
	}

	public function setAccountingCustomerParty(?Invoice\AccountingCustomerParty $accountingCustomerParty): self
	{
		$this->accountingCustomerParty = $accountingCustomerParty;
		return $this;
	}

	public function getAnonymousCustomerParty(): ?Invoice\AnonymousCustomerParty
	{
		return $this->anonymousCustomerParty;
	}

	public function setAnonymousCustomerParty(?Invoice\AnonymousCustomerParty $anonymousCustomerParty): self
	{
		$this->anonymousCustomerParty = $anonymousCustomerParty;
		return $this;
	}

	public function getBuyerCustomerParty(): ?Invoice\BuyerCustomerParty
	{
		return $this->buyerCustomerParty;
	}

	public function setBuyerCustomerParty(?Invoice\BuyerCustomerParty $buyerCustomerParty): self
	{
		$this->buyerCustomerParty = $buyerCustomerParty;
		return $this;
	}

	public function getOrderReferences(): ?Invoice\OrderReferences
	{
		return $this->orderReferences;
	}

	public function setOrderReferences(?Invoice\OrderReferences $orderReferences): self
	{
		$this->orderReferences = $orderReferences;
		return $this;
	}

	public function getDeliveryNoteReferences(): ?Invoice\DeliveryNoteReferences
	{
		return $this->deliveryNoteReferences;
	}

	public function setDeliveryNoteReferences(?Invoice\DeliveryNoteReferences $deliveryNoteReferences): self
	{
		$this->deliveryNoteReferences = $deliveryNoteReferences;
		return $this;
	}

	public function getOriginalDocumentReferences(): ?Invoice\OriginalDocumentReferences
	{
		return $this->originalDocumentReferences;
	}

	public function setOriginalDocumentReferences(?Invoice\OriginalDocumentReferences $originalDocumentReferences): self
	{
		$this->originalDocumentReferences = $originalDocumentReferences;
		return $this;
	}

	public function getContractReferences(): ?Invoice\ContractReferences
	{
		return $this->contractReferences;
	}

	public function setContractReferences(?Invoice\ContractReferences $contractReferences): self
	{
		$this->contractReferences = $contractReferences;
		return $this;
	}

	public function getDelivery(): ?Invoice\Delivery
	{
		return $this->delivery;
	}

	public function setDelivery(?Invoice\Delivery $delivery): self
	{
		$this->delivery = $delivery;
		return $this;
	}

	public function getInvoiceLines(): Invoice\InvoiceLines
	{
		return $this->invoiceLines;
	}

	public function setInvoiceLines(Invoice\InvoiceLines $invoiceLines): self
	{
		$this->invoiceLines = $invoiceLines;
		return $this;
	}

	public function getNonTaxedDeposits(): ?Invoice\NonTaxedDeposits
	{
		return $this->nonTaxedDeposits;
	}

	public function setNonTaxedDeposits(?Invoice\NonTaxedDeposits $nonTaxedDeposits): self
	{
		$this->nonTaxedDeposits = $nonTaxedDeposits;
		return $this;
	}

	public function getTaxedDeposits(): ?Invoice\TaxedDeposits
	{
		return $this->taxedDeposits;
	}

	public function setTaxedDeposits(?Invoice\TaxedDeposits $taxedDeposits): self
	{
		$this->taxedDeposits = $taxedDeposits;
		return $this;
	}

	public function getTaxTotal(): Invoice\TaxTotal
	{
		return $this->taxTotal;
	}

	public function setTaxTotal(Invoice\TaxTotal $taxTotal): self
	{
		$this->taxTotal = $taxTotal;
		return $this;
	}

	public function getLegalMonetaryTotal(): Invoice\LegalMonetaryTotal
	{
		return $this->legalMonetaryTotal;
	}

	public function setLegalMonetaryTotal(Invoice\LegalMonetaryTotal $legalMonetaryTotal): self
	{
		$this->legalMonetaryTotal = $legalMonetaryTotal;
		return $this;
	}

	public function getPaymentMeans(): ?Invoice\PaymentMeans
	{
		return $this->paymentMeans;
	}

	public function setPaymentMeans(?Invoice\PaymentMeans $paymentMeans): self
	{
		$this->paymentMeans = $paymentMeans;
		return $this;
	}

	public function getSupplementsList(): ?Invoice\SupplementsList
	{
		return $this->supplementsList;
	}

	public function setSupplementsList(?Invoice\SupplementsList $supplementsList): self
	{
		$this->supplementsList = $supplementsList;
		return $this;
	}

	public function getVersion(): string
	{
		return $this->version;
	}

	public function setVersion(string $version): self
	{
		Restriction::pattern($version, '[0-9]+\\.[0-9]+(\\.[0-9]+)?');
		$this->version = $version;
		return $this;
	}

}