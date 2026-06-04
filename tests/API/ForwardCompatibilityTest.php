<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC\API;

use Adawolfa\ISDOC\Schema\Invoice as S;
use PHPUnit\Framework\TestCase;

/**
 * Forward-compatibility guard for the codelist constants.
 *
 * In 2.0 the SCREAMING_SNAKE_CASE class constants are replaced with PascalCase enums
 * ({@see S\DocumentType}, {@see S\VATCalculationMethod}, {@see S\LocalReverseChargeCode},
 * {@see S\PaymentMeansCode}, {@see S\BatchOrSerialNumber}). 1.x ships those as plain classes with
 * identically-named constants so that code migrated to the new convention upgrades to 2.0 unchanged.
 *
 * Two contracts are pinned here:
 *
 *  - {@see self::testNewConstantsHoldStableValues()} pins each new constant to its scalar value, which
 *    must match the backing value of the corresponding 2.0 enum case.
 *  - {@see self::testDeprecatedConstantsBridgeToNewClasses()} pins each deprecated constant to its new
 *    counterpart, proving the bridge preserves the original 1.x value.
 */
final class ForwardCompatibilityTest extends TestCase
{

	public function testNewConstantsHoldStableValues(): void
	{
		self::assertSame(1, S\DocumentType::Invoice);
		self::assertSame(2, S\DocumentType::CreditNote);
		self::assertSame(3, S\DocumentType::DebitNote);
		self::assertSame(4, S\DocumentType::ProformaInvoiceNoVAT);
		self::assertSame(5, S\DocumentType::AdvanceInvoiceWithVAT);
		self::assertSame(6, S\DocumentType::CreditNoteForAdvanceInvoiceWithVAT);
		self::assertSame(7, S\DocumentType::SimplifiedTaxDocument);

		self::assertSame(0, S\VATCalculationMethod::FromTheBottom);
		self::assertSame(1, S\VATCalculationMethod::FromTheTop);

		self::assertSame('1', S\LocalReverseChargeCode::DeliveryOfGold);
		self::assertSame('2', S\LocalReverseChargeCode::TradeWithEmissionAllowances);
		self::assertSame('4', S\LocalReverseChargeCode::DeliveryOfDeveloperOrAssemblyWork);
		self::assertSame('5', S\LocalReverseChargeCode::WasteSeeAppendix5OfVATBill);

		self::assertSame(10, S\PaymentMeansCode::CashPayment);
		self::assertSame(20, S\PaymentMeansCode::ChequePayment);
		self::assertSame(31, S\PaymentMeansCode::CreditTransfer);
		self::assertSame(42, S\PaymentMeansCode::MoneyTransferToAnAccount);
		self::assertSame(48, S\PaymentMeansCode::CardPayment);
		self::assertSame(49, S\PaymentMeansCode::DirectDebit);
		self::assertSame(50, S\PaymentMeansCode::PaymentByPostgiro);
		self::assertSame(97, S\PaymentMeansCode::CompositionBetweenPartners);

		self::assertSame('B', S\BatchOrSerialNumber::Batch);
		self::assertSame('S', S\BatchOrSerialNumber::SerialNumber);
	}

	public function testDeprecatedConstantsBridgeToNewClasses(): void
	{
		self::assertSame(S\DocumentType::Invoice, S::DOCUMENT_TYPE_INVOICE);
		self::assertSame(S\DocumentType::CreditNote, S::DOCUMENT_TYPE_CREDIT_NOTE);
		self::assertSame(S\DocumentType::DebitNote, S::DOCUMENT_TYPE_DEBIT_NOTE);
		self::assertSame(S\DocumentType::ProformaInvoiceNoVAT, S::DOCUMENT_TYPE_PROFORMA_INVOICE_NO_VAT);
		self::assertSame(S\DocumentType::AdvanceInvoiceWithVAT, S::DOCUMENT_TYPE_ADVANCE_INVOICE_WITH_VAT);
		self::assertSame(S\DocumentType::CreditNoteForAdvanceInvoiceWithVAT, S::DOCUMENT_TYPE_CREDIT_NOTE_FOR_ADVANCE_INVOICE_WITH_VAT);
		self::assertSame(S\DocumentType::SimplifiedTaxDocument, S::DOCUMENT_TYPE_SIMPLIFIED_TAX_DOCUMENT);

		self::assertSame(S\VATCalculationMethod::FromTheBottom, S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_BOTTOM);
		self::assertSame(S\VATCalculationMethod::FromTheTop, S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP);

		self::assertSame(S\LocalReverseChargeCode::DeliveryOfGold, S\LocalReverseCharge::LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_GOLD);
		self::assertSame(S\LocalReverseChargeCode::TradeWithEmissionAllowances, S\LocalReverseCharge::LOCAL_REVERSE_CHARGE_CODE_TRADE_WITH_EMISSION_ALLOWANCES);
		self::assertSame(S\LocalReverseChargeCode::DeliveryOfDeveloperOrAssemblyWork, S\LocalReverseCharge::LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_DEVELOPER_OR_ASSEMBLY_WORK);
		self::assertSame(S\LocalReverseChargeCode::WasteSeeAppendix5OfVATBill, S\LocalReverseCharge::LOCAL_REVERSE_CHARGE_CODE_WASTE_SEE_APPENDIX_5_OF_VAT_BILL);

		self::assertSame(S\PaymentMeansCode::CashPayment, S\Payment::PAYMENT_MEANS_CODE_CASH_PAYMENT);
		self::assertSame(S\PaymentMeansCode::ChequePayment, S\Payment::PAYMENT_MEANS_CODE_CHEQUE_PAYMENT);
		self::assertSame(S\PaymentMeansCode::CreditTransfer, S\Payment::PAYMENT_MEANS_CODE_CREDIT_TRANSFER);
		self::assertSame(S\PaymentMeansCode::MoneyTransferToAnAccount, S\Payment::PAYMENT_MEANS_CODE_MONEY_TRANSFER_TO_AN_ACCOUNT);
		self::assertSame(S\PaymentMeansCode::CardPayment, S\Payment::PAYMENT_MEANS_CODE_CARD_PAYMENT);
		self::assertSame(S\PaymentMeansCode::DirectDebit, S\Payment::PAYMENT_MEANS_CODE_DIRECT_DEBIT);
		self::assertSame(S\PaymentMeansCode::PaymentByPostgiro, S\Payment::PAYMENT_MEANS_CODE_PAYMENT_BY_POSTGIRO);
		self::assertSame(S\PaymentMeansCode::CompositionBetweenPartners, S\Payment::PAYMENT_MEANS_CODE_COMPOSITION_BETWEEN_PARTNERS);

		self::assertSame(S\BatchOrSerialNumber::Batch, S\StoreBatch::BATCH_OR_SERIAL_NUMBER_BATCH);
		self::assertSame(S\BatchOrSerialNumber::SerialNumber, S\StoreBatch::BATCH_OR_SERIAL_NUMBER_SERIAL_NUMBER);
	}

}
