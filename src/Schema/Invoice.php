<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema;

use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;
use DateTimeInterface;

/**
 * Document root element, subtype is stored in DocumentType element.
 */
class Invoice implements Entity
{

	use Backing;

	/** Document type. */
	public Invoice\DocumentType $documentType {
		/** @throws Exception */
		get => $this->node->getEnumOrThrow('DocumentType', Invoice\DocumentType::class);
		set { $this->node->setEnum('DocumentType', $value); }
	}

	/** Document subtype. Codelist is maintained and published by subject specified in SubDocumentTypeOrigin. */
	public ?string $subDocumentType {
		get => $this->node->getString('SubDocumentType');
		set {
			$this->node->setString('SubDocumentType', $value);
		}
	}

	/** Maintainer of subdocument type code list. */
	public ?string $subDocumentTypeOrigin {
		get => $this->node->getString('SubDocumentTypeOrigin');
		set {
			$this->node->setString('SubDocumentTypeOrigin', $value);
		}
	}

	/** Identification of target consolidator. Values are extended list of bank codes maintained and published by ČBA. */
	public ?string $targetConsolidator {
		get => $this->node->getString('TargetConsolidator');
		set {
			$this->node->setString('TargetConsolidator', $value);
		}
	}

	/** Client identification in the issuer system. Used mainly in B2C systems of companies issuing large volume of invoices. */
	public ?string $clientOnTargetConsolidator {
		get => $this->node->getString('ClientOnTargetConsolidator');
		set {
			$this->node->setString('ClientOnTargetConsolidator', $value);
		}
	}

	/** Complete bank account number of invoice receiver. Used mainly in B2C systems of companies issuing large volume of invoices. */
	public ?string $clientBankAccount {
		get => $this->node->getString('ClientBankAccount');
		set {
			$this->node->setString('ClientBankAccount', $value);
		}
	}

	/** Human readable document number. */
	public string $id {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('ID');
		set {
			$this->node->setString('ID', $value);
		}
	}

	/** GUID identifier produced by emitting system. */
	public string $uuid {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('UUID');
		set {
			Restriction::pattern($value, '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}');
			$this->node->setString('UUID', $value);
		}
	}

	/** Flag for state governed documents. */
	public ?bool $egovFlag {
		/** @throws Exception */
		get => $this->node->getBool('EgovFlag');
		set {
			$this->node->setBool('EgovFlag', $value);
		}
	}

	/** Unique identifier inside ISDS system. */
	public ?string $isds_id {
		get => $this->node->getString('ISDS_ID');
		set {
			$this->node->setString('ISDS_ID', $value);
		}
	}

	/** File number. */
	public ?string $file {
		get => $this->node->getString('FileReference');
		set {
			$this->node->setString('FileReference', $value);
		}
	}

	/** Reference number. */
	public ?string $referenceNumber {
		get => $this->node->getString('ReferenceNumber');
		set {
			$this->node->setString('ReferenceNumber', $value);
		}
	}

	/** Collection of classifiers. */
	public ?Invoice\EgovClassifiers $egovClassifiers {
		get => $this->node->getChild('EgovClassifiers', Invoice\EgovClassifiers::class);
		set { $this->node->setChild('EgovClassifiers', $value); }
	}

	/** Identification of issuing system. */
	public ?string $issuingSystem {
		get => $this->node->getString('IssuingSystem');
		set {
			Restriction::maxLength($value, 80);
			$this->node->setString('IssuingSystem', $value);
		}
	}

	/** Issue date. */
	public DateTimeInterface $issueDate {
		/** @throws Exception */
		get => $this->node->getDateOrThrow('IssueDate');
		set {
			$this->node->setDate('IssueDate', $value);
		}
	}

	/** Tax point date. */
	public ?DateTimeInterface $taxPointDate {
		/** @throws Exception */
		get => $this->node->getDate('TaxPointDate');
		set {
			$this->node->setDate('TaxPointDate', $value);
		}
	}

	/** VAT is applicable. */
	public bool $vatApplicable {
		/** @throws Exception */
		get => $this->node->getBoolOrThrow('VATApplicable');
		set {
			$this->node->setBool('VATApplicable', $value);
		}
	}

	/** Reference to agreement about acceptance of electronic invoices. */
	public Invoice\Note $electronicPossibilityAgreement {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('ElectronicPossibilityAgreementReference', Invoice\Note::class);
		set { $this->node->setChild('ElectronicPossibilityAgreementReference', $value); }
	}

	/** Note. */
	public ?Invoice\Note $note {
		get => $this->node->getChild('Note', Invoice\Note::class);
		set { $this->node->setChild('Note', $value); }
	}

	/** Currency code. */
	public string $localCurrencyCode {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('LocalCurrencyCode');
		set {
			Restriction::length($value, 3);
			$this->node->setString('LocalCurrencyCode', $value);
		}
	}

	/** Currency code. */
	public ?string $foreignCurrencyCode {
		get => $this->node->getString('ForeignCurrencyCode');
		set {
			Restriction::length($value, 3);
			$this->node->setString('ForeignCurrencyCode', $value);
		}
	}

	/** Foreign currency exchange rate (if foreign currency is used), otherwise 1. */
	public Number $currRate {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('CurrRate');
		set {
			$this->node->setNumber('CurrRate', $value);
		}
	}

	/** Reference foreign currency exchange rate, usually 1. */
	public Number $refCurrRate {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('RefCurrRate');
		set {
			$this->node->setNumber('RefCurrRate', $value);
		}
	}

	/** Arbitrary fragment of user-defined elements. Elements must use their own namespace. */
	public ?Invoice\Extensions $extensions {
		get => $this->node->getChild('Extensions', Invoice\Extensions::class);
		set { $this->node->setChild('Extensions', $value); }
	}

	/** Supplier, accounting entity in Commercial Register. */
	public Invoice\AccountingSupplierParty $accountingSupplierParty {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('AccountingSupplierParty', Invoice\AccountingSupplierParty::class);
		set { $this->node->setChild('AccountingSupplierParty', $value); }
	}

	/** Supplier, invoicing address. */
	public ?Invoice\SellerSupplierParty $sellerSupplierParty {
		get => $this->node->getChild('SellerSupplierParty', Invoice\SellerSupplierParty::class);
		set { $this->node->setChild('SellerSupplierParty', $value); }
	}

	/** Anonymous receiver of simplified tax document. */
	public ?Invoice\AnonymousCustomerParty $anonymousCustomerParty {
		get => $this->node->getChild('AnonymousCustomerParty', Invoice\AnonymousCustomerParty::class);
		set { $this->node->setChild('AnonymousCustomerParty', $value); }
	}

	/** Customer, accounting entity in Commercial Register. */
	public ?Invoice\AccountingCustomerParty $accountingCustomerParty {
		get => $this->node->getChild('AccountingCustomerParty', Invoice\AccountingCustomerParty::class);
		set { $this->node->setChild('AccountingCustomerParty', $value); }
	}

	/** Purchaser, invoicing address. */
	public ?Invoice\BuyerCustomerParty $buyerCustomerParty {
		get => $this->node->getChild('BuyerCustomerParty', Invoice\BuyerCustomerParty::class);
		set { $this->node->setChild('BuyerCustomerParty', $value); }
	}

	/** Header collection of referenced purchase order(s). */
	public ?Invoice\OrderReferences $orderReferences {
		get => $this->node->getChild('OrderReferences', Invoice\OrderReferences::class);
		set { $this->node->setChild('OrderReferences', $value); }
	}

	/** Header collection of referenced delivery notes. */
	public ?Invoice\DeliveryNoteReferences $deliveryNoteReferences {
		get => $this->node->getChild('DeliveryNoteReferences', Invoice\DeliveryNoteReferences::class);
		set { $this->node->setChild('DeliveryNoteReferences', $value); }
	}

	/** Header collection of referenced original documents. */
	public ?Invoice\OriginalDocumentReferences $originalDocumentReferences {
		get => $this->node->getChild('OriginalDocumentReferences', Invoice\OriginalDocumentReferences::class);
		set { $this->node->setChild('OriginalDocumentReferences', $value); }
	}

	/** Related contracts. */
	public ?Invoice\ContractReferences $contractReferences {
		get => $this->node->getChild('ContractReferences', Invoice\ContractReferences::class);
		set { $this->node->setChild('ContractReferences', $value); }
	}

	/** Information about delivery. */
	public ?Invoice\Delivery $delivery {
		get => $this->node->getChild('Delivery', Invoice\Delivery::class);
		set { $this->node->setChild('Delivery', $value); }
	}

	/** Invoice lines collection. */
	public Invoice\InvoiceLines $invoiceLines {
		get => $this->node->ensureChild('InvoiceLines', Invoice\InvoiceLines::class);
		set { $this->node->setChild('InvoiceLines', $value); }
	}

	/** Collection of proforma invoices (without VAT). */
	public ?Invoice\NonTaxedDeposits $nonTaxedDeposits {
		get => $this->node->getChild('NonTaxedDeposits', Invoice\NonTaxedDeposits::class);
		set { $this->node->setChild('NonTaxedDeposits', $value); }
	}

	/** Collection of taxed deposits (advance invoices with VAT). */
	public ?Invoice\TaxedDeposits $taxedDeposits {
		get => $this->node->getChild('TaxedDeposits', Invoice\TaxedDeposits::class);
		set { $this->node->setChild('TaxedDeposits', $value); }
	}

	/** Information about a total amount of a particular type of tax. */
	public Invoice\TaxTotal $taxTotal {
		get => $this->node->ensureChild('TaxTotal', Invoice\TaxTotal::class);
		set { $this->node->setChild('TaxTotal', $value); }
	}

	/** Collection of total amounts on document ending with payable amount. */
	public Invoice\LegalMonetaryTotal $legalMonetaryTotal {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('LegalMonetaryTotal', Invoice\LegalMonetaryTotal::class);
		set { $this->node->setChild('LegalMonetaryTotal', $value); }
	}

	/** Information about payment means. */
	public ?Invoice\PaymentMeans $paymentMeans {
		get => $this->node->getChild('PaymentMeans', Invoice\PaymentMeans::class);
		set { $this->node->setChild('PaymentMeans', $value); }
	}

	/** Collection of document attachments. Exactly one attachment can be document preview marked by preview="true". */
	public ?Invoice\SupplementsList $supplementsList {
		get => $this->node->getChild('SupplementsList', Invoice\SupplementsList::class);
		set { $this->node->setChild('SupplementsList', $value); }
	}

	/** ISDOC version number. */
	public string $version {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('@version');
		set {
			Restriction::pattern($value, '[0-9]+\\.[0-9]+(\\.[0-9]+)?');
			$this->node->setString('@version', $value);
		}
	}

	public function __construct(
		Invoice\DocumentType $documentType,
		string $id,
		string $uuid,
		DateTimeInterface $issueDate,
		bool $vatApplicable,
		Invoice\Note $electronicPossibilityAgreement,
		string $localCurrencyCode,
		Number $currRate,
		Number $refCurrRate,
		Invoice\AccountingSupplierParty $accountingSupplierParty,
		Invoice\InvoiceLines $invoiceLines,
		Invoice\TaxTotal $taxTotal,
		Invoice\LegalMonetaryTotal $legalMonetaryTotal,
		string $version,
	)
	{
		$this->documentType = $documentType;
		$this->id = $id;
		$this->uuid = $uuid;
		$this->issueDate = $issueDate;
		$this->vatApplicable = $vatApplicable;
		$this->electronicPossibilityAgreement = $electronicPossibilityAgreement;
		$this->localCurrencyCode = $localCurrencyCode;
		$this->currRate = $currRate;
		$this->refCurrRate = $refCurrRate;
		$this->accountingSupplierParty = $accountingSupplierParty;
		$this->invoiceLines = $invoiceLines;
		$this->taxTotal = $taxTotal;
		$this->legalMonetaryTotal = $legalMonetaryTotal;
		$this->version = $version;
	}

}