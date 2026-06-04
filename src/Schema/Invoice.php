<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Deprecations;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\Schema\Invoice\DocumentType;
use Adawolfa\ISDOC\ToArray;
use BcMath;
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
 * @property string|BcMath\Number                    $currRate
 * @property string|BcMath\Number                    $refCurrRate
 * @property Invoice\AccountingSupplierParty         $accountingSupplierParty
 * @property Invoice\SellerSupplierParty|null        $sellerSupplierParty
 * @property Invoice\AnonymousCustomerParty|null     $anonymousCustomerParty
 * @property Invoice\AccountingCustomerParty|null    $accountingCustomerParty
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

	/** @deprecated use {@see DocumentType::Invoice} instead */
	public const int DOCUMENT_TYPE_INVOICE = DocumentType::Invoice;

	/** @deprecated use {@see DocumentType::CreditNote} instead */
	public const int DOCUMENT_TYPE_CREDIT_NOTE = DocumentType::CreditNote;

	/** @deprecated use {@see DocumentType::DebitNote} instead */
	public const int DOCUMENT_TYPE_DEBIT_NOTE = DocumentType::DebitNote;

	/** @deprecated use {@see DocumentType::ProformaInvoiceNoVAT} instead */
	public const int DOCUMENT_TYPE_PROFORMA_INVOICE_NO_VAT = DocumentType::ProformaInvoiceNoVAT;

	/** @deprecated use {@see DocumentType::AdvanceInvoiceWithVAT} instead */
	public const int DOCUMENT_TYPE_ADVANCE_INVOICE_WITH_VAT = DocumentType::AdvanceInvoiceWithVAT;

	/** @deprecated use {@see DocumentType::CreditNoteForAdvanceInvoiceWithVAT} instead */
	public const int DOCUMENT_TYPE_CREDIT_NOTE_FOR_ADVANCE_INVOICE_WITH_VAT = DocumentType::CreditNoteForAdvanceInvoiceWithVAT;

	/** @deprecated use {@see DocumentType::SimplifiedTaxDocument} instead */
	public const int DOCUMENT_TYPE_SIMPLIFIED_TAX_DOCUMENT = DocumentType::SimplifiedTaxDocument;

	/** Document type. */
	#[Map('DocumentType')]
	private int $documentType;

	/** Document subtype. Codelist is maintained and published by subject specified in SubDocumentTypeOrigin. */
	#[Map('SubDocumentType')]
	private ?string $subDocumentType = null;

	/** Maintainer of subdocument type code list. */
	#[Map('SubDocumentTypeOrigin')]
	private ?string $subDocumentTypeOrigin = null;

	/** Identification of target consolidator. Values are extended list of bank codes maintained and published by ČBA. */
	#[Map('TargetConsolidator')]
	private ?string $targetConsolidator = null;

	/** Client identification in the issuer system. Used mainly in B2C systems of companies issuing large volume of invoices. */
	#[Map('ClientOnTargetConsolidator')]
	private ?string $clientOnTargetConsolidator = null;

	/** Complete bank account number of invoice receiver. Used mainly in B2C systems of companies issuing large volume of invoices. */
	#[Map('ClientBankAccount')]
	private ?string $clientBankAccount = null;

	/** Human readable document number. */
	#[Map('ID')]
	private string $id;

	/** GUID identifier produced by emitting system. */
	#[Map('UUID')]
	private string $uuid;

	/** Flag for state governed documents. */
	#[Map('EgovFlag')]
	private ?bool $egovFlag = null;

	/** Unique identifier inside ISDS system. */
	#[Map('ISDS_ID')]
	private ?string $isds_id = null;

	/** File number. */
	#[Map('FileReference')]
	private ?string $file = null;

	/** Reference number. */
	#[Map('ReferenceNumber')]
	private ?string $referenceNumber = null;

	/** Collection of classifiers. */
	#[Map('EgovClassifiers')]
	private ?Invoice\EgovClassifiers $egovClassifiers = null;

	/** Identification of issuing system. */
	#[Map('IssuingSystem')]
	private ?string $issuingSystem = null;

	/** Issue date. */
	#[Map('IssueDate')]
	private DateTimeInterface $issueDate;

	/** Tax point date. */
	#[Map('TaxPointDate')]
	private ?DateTimeInterface $taxPointDate = null;

	/** VAT is applicable. */
	#[Map('VATApplicable')]
	private bool $vatApplicable;

	/** Reference to agreement about acceptance of electronic invoices. */
	#[Map('ElectronicPossibilityAgreementReference')]
	private Invoice\Note $electronicPossibilityAgreement;

	/** Note. */
	#[Map('Note')]
	private ?Invoice\Note $note = null;

	/** Currency code. */
	#[Map('LocalCurrencyCode')]
	private string $localCurrencyCode;

	/** Currency code. */
	#[Map('ForeignCurrencyCode')]
	private ?string $foreignCurrencyCode = null;

	/** Foreign currency exchange rate (if foreign currency is used), otherwise 1. */
	#[Map('CurrRate')]
	private string $currRate;

	/** Reference foreign currency exchange rate, usually 1. */
	#[Map('RefCurrRate')]
	private string $refCurrRate;

	/** Supplier, accounting entity in Commercial Register. */
	#[Map('AccountingSupplierParty')]
	private Invoice\AccountingSupplierParty $accountingSupplierParty;

	/** Supplier, invoicing address. */
	#[Map('SellerSupplierParty')]
	private ?Invoice\SellerSupplierParty $sellerSupplierParty = null;

	/** Anonymous receiver of simplified tax document. */
	#[Map('AnonymousCustomerParty')]
	private ?Invoice\AnonymousCustomerParty $anonymousCustomerParty = null;

	/** Customer, accounting entity in Commercial Register. */
	#[Map('AccountingCustomerParty')]
	private ?Invoice\AccountingCustomerParty $accountingCustomerParty = null;

	/** Purchaser, invoicing address. */
	#[Map('BuyerCustomerParty')]
	private ?Invoice\BuyerCustomerParty $buyerCustomerParty = null;

	/** Header collection of referenced purchase order(s). */
	#[Map('OrderReferences')]
	private ?Invoice\OrderReferences $orderReferences = null;

	/** Header collection of referenced delivery notes. */
	#[Map('DeliveryNoteReferences')]
	private ?Invoice\DeliveryNoteReferences $deliveryNoteReferences = null;

	/** Header collection of referenced original documents. */
	#[Map('OriginalDocumentReferences')]
	private ?Invoice\OriginalDocumentReferences $originalDocumentReferences = null;

	/** Related contracts. */
	#[Map('ContractReferences')]
	private ?Invoice\ContractReferences $contractReferences = null;

	/** Information about delivery. */
	#[Map('Delivery')]
	private ?Invoice\Delivery $delivery = null;

	/** Invoice lines collection. */
	#[Map('InvoiceLines')]
	private Invoice\InvoiceLines $invoiceLines;

	/** Collection of proforma invoices (without VAT). */
	#[Map('NonTaxedDeposits')]
	private ?Invoice\NonTaxedDeposits $nonTaxedDeposits = null;

	/** Collection of taxed deposits (advance invoices with VAT). */
	#[Map('TaxedDeposits')]
	private ?Invoice\TaxedDeposits $taxedDeposits = null;

	/** Information about a total amount of a particular type of tax. */
	#[Map('TaxTotal')]
	private Invoice\TaxTotal $taxTotal;

	/** Collection of total amounts on document ending with payable amount. */
	#[Map('LegalMonetaryTotal')]
	private Invoice\LegalMonetaryTotal $legalMonetaryTotal;

	/** Information about payment means. */
	#[Map('PaymentMeans')]
	private ?Invoice\PaymentMeans $paymentMeans = null;

	/** Collection of document attachments. Exactly one attachment can be document preview marked by preview="true". */
	#[Map('SupplementsList')]
	private ?Invoice\SupplementsList $supplementsList = null;

	/** ISDOC version number. */
	#[Map('@version')]
	private string $version;

	public function __construct(
		int                             $documentType,
		string                          $id,
		string                          $uuid,
		DateTimeInterface               $issueDate,
		bool                            $vatApplicable,
		Invoice\Note                    $electronicPossibilityAgreement,
		string                          $localCurrencyCode,
		string|BcMath\Number            $currRate,
		string|BcMath\Number            $refCurrRate,
		Invoice\AccountingSupplierParty $accountingSupplierParty,
		Invoice\InvoiceLines            $invoiceLines,
		Invoice\TaxTotal                $taxTotal,
		Invoice\LegalMonetaryTotal      $legalMonetaryTotal,
		string                          $version,
	)
	{
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

	/** @deprecated Method accessors are deprecated, use {@see $documentType} property instead. */
	public function getDocumentType(): int
	{
		return $this->documentType;
	}

	/** @deprecated Method accessors are deprecated, use {@see $documentType} property instead. */
	public function setDocumentType(int $documentType): self
	{
		Restriction::enumeration($documentType, [
			DocumentType::Invoice,
			DocumentType::CreditNote,
			DocumentType::DebitNote,
			DocumentType::ProformaInvoiceNoVAT,
			DocumentType::AdvanceInvoiceWithVAT,
			DocumentType::CreditNoteForAdvanceInvoiceWithVAT,
			DocumentType::SimplifiedTaxDocument,
		]);
		$this->documentType = $documentType;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $subDocumentType} property instead. */
	public function getSubDocumentType(): ?string
	{
		return $this->subDocumentType;
	}

	/** @deprecated Method accessors are deprecated, use {@see $subDocumentType} property instead. */
	public function setSubDocumentType(?string $subDocumentType): self
	{
		$this->subDocumentType = $subDocumentType;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $subDocumentTypeOrigin} property instead. */
	public function getSubDocumentTypeOrigin(): ?string
	{
		return $this->subDocumentTypeOrigin;
	}

	/** @deprecated Method accessors are deprecated, use {@see $subDocumentTypeOrigin} property instead. */
	public function setSubDocumentTypeOrigin(?string $subDocumentTypeOrigin): self
	{
		$this->subDocumentTypeOrigin = $subDocumentTypeOrigin;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $targetConsolidator} property instead. */
	public function getTargetConsolidator(): ?string
	{
		return $this->targetConsolidator;
	}

	/** @deprecated Method accessors are deprecated, use {@see $targetConsolidator} property instead. */
	public function setTargetConsolidator(?string $targetConsolidator): self
	{
		$this->targetConsolidator = $targetConsolidator;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $clientOnTargetConsolidator} property instead. */
	public function getClientOnTargetConsolidator(): ?string
	{
		return $this->clientOnTargetConsolidator;
	}

	/** @deprecated Method accessors are deprecated, use {@see $clientOnTargetConsolidator} property instead. */
	public function setClientOnTargetConsolidator(?string $clientOnTargetConsolidator): self
	{
		$this->clientOnTargetConsolidator = $clientOnTargetConsolidator;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $clientBankAccount} property instead. */
	public function getClientBankAccount(): ?string
	{
		return $this->clientBankAccount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $clientBankAccount} property instead. */
	public function setClientBankAccount(?string $clientBankAccount): self
	{
		$this->clientBankAccount = $clientBankAccount;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function getId(): string
	{
		return $this->id;
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function setId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $uuid} property instead. */
	public function getUuid(): string
	{
		return $this->uuid;
	}

	/** @deprecated Method accessors are deprecated, use {@see $uuid} property instead. */
	public function setUuid(string $uuid): self
	{
		Restriction::pattern($uuid, '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}');
		$this->uuid = $uuid;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $egovFlag} property instead. */
	public function getEgovFlag(): ?bool
	{
		return $this->egovFlag;
	}

	/** @deprecated Method accessors are deprecated, use {@see $egovFlag} property instead. */
	public function setEgovFlag(?bool $egovFlag): self
	{
		$this->egovFlag = $egovFlag;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $isds_id} property instead. */
	public function getIsds_id(): ?string
	{
		return $this->isds_id;
	}

	/** @deprecated Method accessors are deprecated, use {@see $isds_id} property instead. */
	public function setIsds_id(?string $isds_id): self
	{
		$this->isds_id = $isds_id;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $file} property instead. */
	public function getFile(): ?string
	{
		return $this->file;
	}

	/** @deprecated Method accessors are deprecated, use {@see $file} property instead. */
	public function setFile(?string $file): self
	{
		$this->file = $file;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $referenceNumber} property instead. */
	public function getReferenceNumber(): ?string
	{
		return $this->referenceNumber;
	}

	/** @deprecated Method accessors are deprecated, use {@see $referenceNumber} property instead. */
	public function setReferenceNumber(?string $referenceNumber): self
	{
		$this->referenceNumber = $referenceNumber;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $egovClassifiers} property instead. */
	public function getEgovClassifiers(): ?Invoice\EgovClassifiers
	{
		return $this->egovClassifiers;
	}

	/** @deprecated Method accessors are deprecated, use {@see $egovClassifiers} property instead. */
	public function setEgovClassifiers(?Invoice\EgovClassifiers $egovClassifiers): self
	{
		$this->egovClassifiers = $egovClassifiers;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $issuingSystem} property instead. */
	public function getIssuingSystem(): ?string
	{
		return $this->issuingSystem;
	}

	/** @deprecated Method accessors are deprecated, use {@see $issuingSystem} property instead. */
	public function setIssuingSystem(?string $issuingSystem): self
	{
		Restriction::maxLength($issuingSystem, 80);
		$this->issuingSystem = $issuingSystem;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $issueDate} property instead. */
	public function getIssueDate(): DateTimeInterface
	{
		return $this->issueDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $issueDate} property instead. */
	public function setIssueDate(DateTimeInterface $issueDate): self
	{
		$this->issueDate = $issueDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxPointDate} property instead. */
	public function getTaxPointDate(): ?DateTimeInterface
	{
		return $this->taxPointDate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxPointDate} property instead. */
	public function setTaxPointDate(?DateTimeInterface $taxPointDate): self
	{
		$this->taxPointDate = $taxPointDate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $vatApplicable} property instead. */
	public function getVatApplicable(): bool
	{
		return $this->vatApplicable;
	}

	/** @deprecated Method accessors are deprecated, use {@see $vatApplicable} property instead. */
	public function setVatApplicable(bool $vatApplicable): self
	{
		$this->vatApplicable = $vatApplicable;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $electronicPossibilityAgreement} property instead. */
	public function getElectronicPossibilityAgreement(): Invoice\Note
	{
		return $this->electronicPossibilityAgreement;
	}

	/** @deprecated Method accessors are deprecated, use {@see $electronicPossibilityAgreement} property instead. */
	public function setElectronicPossibilityAgreement(Invoice\Note $electronicPossibilityAgreement): self
	{
		$this->electronicPossibilityAgreement = $electronicPossibilityAgreement;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $note} property instead. */
	public function getNote(): ?Invoice\Note
	{
		return $this->note;
	}

	/** @deprecated Method accessors are deprecated, use {@see $note} property instead. */
	public function setNote(?Invoice\Note $note): self
	{
		$this->note = $note;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $localCurrencyCode} property instead. */
	public function getLocalCurrencyCode(): string
	{
		return $this->localCurrencyCode;
	}

	/** @deprecated Method accessors are deprecated, use {@see $localCurrencyCode} property instead. */
	public function setLocalCurrencyCode(string $localCurrencyCode): self
	{
		Restriction::length($localCurrencyCode, 3);
		$this->localCurrencyCode = $localCurrencyCode;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $foreignCurrencyCode} property instead. */
	public function getForeignCurrencyCode(): ?string
	{
		return $this->foreignCurrencyCode;
	}

	/** @deprecated Method accessors are deprecated, use {@see $foreignCurrencyCode} property instead. */
	public function setForeignCurrencyCode(?string $foreignCurrencyCode): self
	{
		Restriction::length($foreignCurrencyCode, 3);
		$this->foreignCurrencyCode = $foreignCurrencyCode;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $currRate} property instead. */
	public function getCurrRate(): string
	{
		return $this->currRate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $currRate} property instead. */
	public function setCurrRate(string|BcMath\Number $currRate): self
	{
		Deprecations::number($currRate);
		$currRate = (string) $currRate;
		Restriction::decimal($currRate);
		$this->currRate = $currRate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $refCurrRate} property instead. */
	public function getRefCurrRate(): string
	{
		return $this->refCurrRate;
	}

	/** @deprecated Method accessors are deprecated, use {@see $refCurrRate} property instead. */
	public function setRefCurrRate(string|BcMath\Number $refCurrRate): self
	{
		Deprecations::number($refCurrRate);
		$refCurrRate = (string) $refCurrRate;
		Restriction::decimal($refCurrRate);
		$this->refCurrRate = $refCurrRate;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $accountingSupplierParty} property instead. */
	public function getAccountingSupplierParty(): Invoice\AccountingSupplierParty
	{
		return $this->accountingSupplierParty;
	}

	/** @deprecated Method accessors are deprecated, use {@see $accountingSupplierParty} property instead. */
	public function setAccountingSupplierParty(Invoice\AccountingSupplierParty $accountingSupplierParty): self
	{
		$this->accountingSupplierParty = $accountingSupplierParty;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $sellerSupplierParty} property instead. */
	public function getSellerSupplierParty(): ?Invoice\SellerSupplierParty
	{
		return $this->sellerSupplierParty;
	}

	/** @deprecated Method accessors are deprecated, use {@see $sellerSupplierParty} property instead. */
	public function setSellerSupplierParty(?Invoice\SellerSupplierParty $sellerSupplierParty): self
	{
		$this->sellerSupplierParty = $sellerSupplierParty;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $anonymousCustomerParty} property instead. */
	public function getAnonymousCustomerParty(): ?Invoice\AnonymousCustomerParty
	{
		return $this->anonymousCustomerParty;
	}

	/** @deprecated Method accessors are deprecated, use {@see $anonymousCustomerParty} property instead. */
	public function setAnonymousCustomerParty(?Invoice\AnonymousCustomerParty $anonymousCustomerParty): self
	{
		$this->anonymousCustomerParty = $anonymousCustomerParty;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $accountingCustomerParty} property instead. */
	public function getAccountingCustomerParty(): ?Invoice\AccountingCustomerParty
	{
		return $this->accountingCustomerParty;
	}

	/** @deprecated Method accessors are deprecated, use {@see $accountingCustomerParty} property instead. */
	public function setAccountingCustomerParty(?Invoice\AccountingCustomerParty $accountingCustomerParty): self
	{
		$this->accountingCustomerParty = $accountingCustomerParty;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $buyerCustomerParty} property instead. */
	public function getBuyerCustomerParty(): ?Invoice\BuyerCustomerParty
	{
		return $this->buyerCustomerParty;
	}

	/** @deprecated Method accessors are deprecated, use {@see $buyerCustomerParty} property instead. */
	public function setBuyerCustomerParty(?Invoice\BuyerCustomerParty $buyerCustomerParty): self
	{
		$this->buyerCustomerParty = $buyerCustomerParty;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $orderReferences} property instead. */
	public function getOrderReferences(): ?Invoice\OrderReferences
	{
		return $this->orderReferences;
	}

	/** @deprecated Method accessors are deprecated, use {@see $orderReferences} property instead. */
	public function setOrderReferences(?Invoice\OrderReferences $orderReferences): self
	{
		$this->orderReferences = $orderReferences;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $deliveryNoteReferences} property instead. */
	public function getDeliveryNoteReferences(): ?Invoice\DeliveryNoteReferences
	{
		return $this->deliveryNoteReferences;
	}

	/** @deprecated Method accessors are deprecated, use {@see $deliveryNoteReferences} property instead. */
	public function setDeliveryNoteReferences(?Invoice\DeliveryNoteReferences $deliveryNoteReferences): self
	{
		$this->deliveryNoteReferences = $deliveryNoteReferences;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $originalDocumentReferences} property instead. */
	public function getOriginalDocumentReferences(): ?Invoice\OriginalDocumentReferences
	{
		return $this->originalDocumentReferences;
	}

	/** @deprecated Method accessors are deprecated, use {@see $originalDocumentReferences} property instead. */
	public function setOriginalDocumentReferences(?Invoice\OriginalDocumentReferences $originalDocumentReferences): self
	{
		$this->originalDocumentReferences = $originalDocumentReferences;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $contractReferences} property instead. */
	public function getContractReferences(): ?Invoice\ContractReferences
	{
		return $this->contractReferences;
	}

	/** @deprecated Method accessors are deprecated, use {@see $contractReferences} property instead. */
	public function setContractReferences(?Invoice\ContractReferences $contractReferences): self
	{
		$this->contractReferences = $contractReferences;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $delivery} property instead. */
	public function getDelivery(): ?Invoice\Delivery
	{
		return $this->delivery;
	}

	/** @deprecated Method accessors are deprecated, use {@see $delivery} property instead. */
	public function setDelivery(?Invoice\Delivery $delivery): self
	{
		$this->delivery = $delivery;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $invoiceLines} property instead. */
	public function getInvoiceLines(): Invoice\InvoiceLines
	{
		return $this->invoiceLines;
	}

	/** @deprecated Method accessors are deprecated, use {@see $invoiceLines} property instead. */
	public function setInvoiceLines(Invoice\InvoiceLines $invoiceLines): self
	{
		$this->invoiceLines = $invoiceLines;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $nonTaxedDeposits} property instead. */
	public function getNonTaxedDeposits(): ?Invoice\NonTaxedDeposits
	{
		return $this->nonTaxedDeposits;
	}

	/** @deprecated Method accessors are deprecated, use {@see $nonTaxedDeposits} property instead. */
	public function setNonTaxedDeposits(?Invoice\NonTaxedDeposits $nonTaxedDeposits): self
	{
		$this->nonTaxedDeposits = $nonTaxedDeposits;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxedDeposits} property instead. */
	public function getTaxedDeposits(): ?Invoice\TaxedDeposits
	{
		return $this->taxedDeposits;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxedDeposits} property instead. */
	public function setTaxedDeposits(?Invoice\TaxedDeposits $taxedDeposits): self
	{
		$this->taxedDeposits = $taxedDeposits;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxTotal} property instead. */
	public function getTaxTotal(): Invoice\TaxTotal
	{
		return $this->taxTotal;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxTotal} property instead. */
	public function setTaxTotal(Invoice\TaxTotal $taxTotal): self
	{
		$this->taxTotal = $taxTotal;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $legalMonetaryTotal} property instead. */
	public function getLegalMonetaryTotal(): Invoice\LegalMonetaryTotal
	{
		return $this->legalMonetaryTotal;
	}

	/** @deprecated Method accessors are deprecated, use {@see $legalMonetaryTotal} property instead. */
	public function setLegalMonetaryTotal(Invoice\LegalMonetaryTotal $legalMonetaryTotal): self
	{
		$this->legalMonetaryTotal = $legalMonetaryTotal;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paymentMeans} property instead. */
	public function getPaymentMeans(): ?Invoice\PaymentMeans
	{
		return $this->paymentMeans;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paymentMeans} property instead. */
	public function setPaymentMeans(?Invoice\PaymentMeans $paymentMeans): self
	{
		$this->paymentMeans = $paymentMeans;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $supplementsList} property instead. */
	public function getSupplementsList(): ?Invoice\SupplementsList
	{
		return $this->supplementsList;
	}

	/** @deprecated Method accessors are deprecated, use {@see $supplementsList} property instead. */
	public function setSupplementsList(?Invoice\SupplementsList $supplementsList): self
	{
		$this->supplementsList = $supplementsList;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $version} property instead. */
	public function getVersion(): string
	{
		return $this->version;
	}

	/** @deprecated Method accessors are deprecated, use {@see $version} property instead. */
	public function setVersion(string $version): self
	{
		Restriction::pattern($version, '[0-9]+\\.[0-9]+(\\.[0-9]+)?');
		$this->version = $version;
		return $this;
	}

}