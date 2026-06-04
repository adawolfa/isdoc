<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC\API;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice as S;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;
use Tests\Adawolfa\ISDOC\Snapshot;

/**
 * Back-compatibility guard for the whole public {@see ISDOC\Schema\Invoice} API surface and the
 * decorated writing API advertised in README.md.
 *
 * The test builds a single, maximally-populated invoice that touches every entity and every property
 * the schema exposes, encodes it to ISDOC through the {@see ISDOC\Manager} facade, decodes it back and
 * reads everything back, asserting the whole object graph survived the round-trip.
 *
 * The flow is written twice, with deliberately explicit, statically-analysable calls so that PHPStan
 * itself verifies the API exists:
 *
 *  - {@see self::testRoundTripViaProperties()} builds and reads through the magic properties
 *    ($invoice->id, $invoice->id = ...), which also pins the magic-property annotations relied upon by
 *    Nette\SmartObject.
 *  - {@see self::testRoundTripViaAccessors()} builds and reads through the get*()/set*() methods.
 *
 * Both paths produce identical XML (the same graph), pinned by a shared snapshot. Required values go
 * through the constructors and collection items through add() — those are identical in both modes.
 *
 * Only the stable, public surface is asserted here; the internal decoder/encoder/hydrator/reflection
 * API is intentionally left out. Coverage of the public surface is verified through phpunit.xml.
 */
final class APICoverageTest extends TestCase
{

	use Snapshot;

	public function testRoundTripViaAccessors(): void
	{
		$this->assertViaGetters($this->roundTrip($this->buildViaSetters()));
	}

	public function testRoundTripViaProperties(): void
	{
		$this->assertViaProperties($this->roundTrip($this->buildViaProperties()));
	}

	/**
	 * Encodes the invoice, pins the exact XML, decodes it back and proves the round-trip is lossless
	 * and idempotent (re-encoding the decoded graph yields the very same XML).
	 */
	private function roundTrip(ISDOC\Invoice $invoice): S
	{
		$manager = ISDOC\Manager::create();

		$xml = $manager->getWriter()->xml($invoice);
		$this->assertSnapshot('api-coverage.xml', $xml);

		$decoded = $manager->getReader()->xml($xml);
		self::assertSame($xml, $manager->getWriter()->xml($decoded), 'Round-trip is not idempotent.');

		$this->assertReferencesResolved($decoded);
		self::assertArrayHasKey('id', $decoded->toArray());

		return $decoded;
	}

	// ---------------------------------------------------------------------------------------------
	// Building the graph through set*() methods.
	// ---------------------------------------------------------------------------------------------

	private function buildViaSetters(): ISDOC\Invoice
	{
		$invoice = new ISDOC\Invoice(
			'2021-0001',
			'00000000-0000-0000-0000-000000001234',
			$this->date('2021-08-16'),
			true,
			'CZK',
			new S\AccountingSupplierParty($this->fullPartyViaSetters('12345678', 'Dodavatel, a. s.')),
		);

		$invoice->setDocumentType(S::DOCUMENT_TYPE_INVOICE);
		$invoice->setSubDocumentType('TAX');
		$invoice->setSubDocumentTypeOrigin('CZ-GFR');
		$invoice->setTargetConsolidator('0800');
		$invoice->setClientOnTargetConsolidator('CLIENT-1');
		$invoice->setClientBankAccount('123456789/0800');
		$invoice->setEgovFlag(true);
		$invoice->setIsds_id('abcdefgh');
		$invoice->setFile('FILE-2021-0001');
		$invoice->setReferenceNumber('REF-2021-0001');
		$invoice->setIssuingSystem('Acme Billing 1.0');
		$invoice->setTaxPointDate($this->date('2021-08-15'));
		$invoice->setForeignCurrencyCode('EUR');
		$invoice->setCurrRate('25.5');
		$invoice->setRefCurrRate('1.0');
		$invoice->setVersion(ISDOC\Invoice::VERSION);

		$invoice->getElectronicPossibilityAgreement()->setContent('Agreed electronically.');
		$invoice->getElectronicPossibilityAgreement()->setLanguageID('cs');
		$invoice->setNote($this->noteViaSetters('Thank you for your business.', 'en'));

		$invoice->setSellerSupplierParty(new S\SellerSupplierParty($this->buildBasicParty('11111111', 'Prodejce, a. s.')));
		$invoice->setAccountingCustomerParty(new S\AccountingCustomerParty($this->fullPartyViaSetters('87654321', 'Odběratel, a. s.')));
		$invoice->setBuyerCustomerParty(new S\BuyerCustomerParty($this->buildBasicParty('22222222', 'Kupující, a. s.')));
		$invoice->setDelivery(new S\Delivery($this->buildBasicParty('33333333', 'Místo dodání, a. s.')));

		$anonymous = new S\AnonymousCustomerParty('ANON-1');
		$anonymous->setIdScheme('https://www.rfc-editor.org/rfc/rfc9562.html');
		$invoice->setAnonymousCustomerParty($anonymous);

		$order = new S\Order('SO-1');
		$order->setExternalOrderID('PO-1');
		$order->setIssueDate($this->date('2021-07-01'));
		$order->setExternalOrderIssueDate($this->date('2021-06-30'));
		$order->setUuid('00000000-0000-0000-0000-0000000000a1');
		$order->setIsds_id('order001');
		$order->setFile('ORDER-FILE');
		$order->setReferenceNumber('ORDER-REF');
		$invoice->setOrderReferences((new S\OrderReferences)->add($order));

		$deliveryNote = new S\DeliveryNote('DN-1');
		$deliveryNote->setIssueDate($this->date('2021-07-10'));
		$deliveryNote->setUuid('00000000-0000-0000-0000-0000000000a2');
		$invoice->setDeliveryNoteReferences((new S\DeliveryNoteReferences)->add($deliveryNote));

		$originalDocument = new S\OriginalDocument('OD-1');
		$originalDocument->setIssueDate($this->date('2021-05-01'));
		$originalDocument->setUuid('00000000-0000-0000-0000-0000000000a3');
		$invoice->setOriginalDocumentReferences((new S\OriginalDocumentReferences)->add($originalDocument));

		$contract = new S\Contract('CT-1', $this->date('2021-01-01'));
		$contract->setUuid('00000000-0000-0000-0000-0000000000a4');
		$contract->setLastValidDate($this->date('2022-01-01'));
		$contract->setIsds_id('contract1');
		$contract->setFile('CONTRACT-FILE');
		$contract->setReferenceNumber('CONTRACT-REF');
		$invoice->setContractReferences((new S\ContractReferences)->add($contract));

		$invoice->getInvoiceLines()->add($this->richLineViaSetters($order, $deliveryNote, $originalDocument, $contract));
		$invoice->getInvoiceLines()->add(new S\InvoiceLine(
			'2',
			'250.0',
			'302.5',
			'52.5',
			'250.0',
			'302.5',
			new S\ClassifiedTaxCategory('21', S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_BOTTOM),
		));

		$nonTaxedDeposit = new S\NonTaxedDeposit('NTD-1', '555', '100.0');
		$nonTaxedDeposit->setDepositAmountCurr('4.0');
		$invoice->setNonTaxedDeposits((new S\NonTaxedDeposits)->add($nonTaxedDeposit));

		$taxedDeposit = new S\TaxedDeposit(
			'TD-1',
			'556',
			'200.0',
			'242.0',
			new S\ClassifiedTaxCategory('21', S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP),
		);
		$taxedDeposit->setTaxableDepositAmountCurr('8.0');
		$taxedDeposit->setTaxInclusiveDepositAmountCurr('9.68');
		$invoice->setTaxedDeposits((new S\TaxedDeposits)->add($taxedDeposit));

		$taxTotal = $invoice->getTaxTotal();
		$taxTotal->setTaxAmount('73.5');
		$taxTotal->setTaxAmountCurr('2.88');
		$taxSubTotal = new S\TaxSubTotal(
			'350.0', '73.5', '423.5', '0.0', '0.0', '0.0', '0.0', '0.0', '0.0', $this->fullTaxCategoryViaSetters(),
		);
		$taxSubTotal->setTaxableAmountCurr('13.72');
		$taxSubTotal->setTaxAmountCurr('2.88');
		$taxSubTotal->setTaxInclusiveAmountCurr('16.6');
		$taxSubTotal->setAlreadyClaimedTaxableAmountCurr('0.0');
		$taxSubTotal->setAlreadyClaimedTaxAmountCurr('0.0');
		$taxSubTotal->setAlreadyClaimedTaxInclusiveAmountCurr('0.0');
		$taxSubTotal->setDifferenceTaxableAmountCurr('0.0');
		$taxSubTotal->setDifferenceTaxAmountCurr('0.0');
		$taxSubTotal->setDifferenceTaxInclusiveAmountCurr('0.0');
		$taxTotal->add($taxSubTotal);

		$total = $invoice->getLegalMonetaryTotal();
		$total->setTaxExclusiveAmount('350.0');
		$total->setTaxExclusiveAmountCurr('13.72');
		$total->setTaxInclusiveAmount('423.5');
		$total->setTaxInclusiveAmountCurr('16.6');
		$total->setAlreadyClaimedTaxExclusiveAmount('0.0');
		$total->setAlreadyClaimedTaxExclusiveAmountCurr('0.0');
		$total->setAlreadyClaimedTaxInclusiveAmount('0.0');
		$total->setAlreadyClaimedTaxInclusiveAmountCurr('0.0');
		$total->setDifferenceTaxExclusiveAmount('0.0');
		$total->setDifferenceTaxExclusiveAmountCurr('0.0');
		$total->setDifferenceTaxInclusiveAmount('0.0');
		$total->setDifferenceTaxInclusiveAmountCurr('0.0');
		$total->setPaidDepositsAmount('0.0');
		$total->setPaidDepositsAmountCurr('0.0');
		$total->setPayableRoundingAmount('0.5');
		$total->setPayableRoundingAmountCurr('0.02');
		$total->setPayableAmount('424.0');
		$total->setPayableAmountCurr('16.62');

		$payment = new S\Payment('424.0', S\Payment::PAYMENT_MEANS_CODE_CREDIT_TRANSFER);
		// Booleans are kept true throughout the round-trip: false booleans do not survive decoding in
		// 1.x (see testBooleanFalseDecodingLimitation()).
		$payment->setPartialPayment(true);
		$payment->setDetails($this->detailsViaSetters());
		$paymentMeans = (new S\PaymentMeans)->add($payment);
		$paymentMeans->setAlternateBankAccounts($this->alternateBankAccountsViaSetters());
		$invoice->setPaymentMeans($paymentMeans);

		$supplement = new S\Supplement(
			'invoice.pdf',
			new S\DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
			'2jmj7l5rSw0yVb/vlWAYkK/YBwk=',
		);
		$supplement->setPreview(true);
		$invoice->setSupplementsList((new S\SupplementsList)->add($supplement));

		return $invoice;
	}

	private function richLineViaSetters(
		S\Order $order,
		S\DeliveryNote $deliveryNote,
		S\OriginalDocument $originalDocument,
		S\Contract $contract,
	): S\InvoiceLine
	{
		$line = new S\InvoiceLine('1', '100.0', '121.0', '21.0', '100.0', '121.0', $this->fullClassifiedTaxCategoryViaSetters());

		$line->setOrder((new S\OrderLine($order))->setLineID('OL-1'));
		$line->setDeliveryNote((new S\DeliveryNoteLine($deliveryNote))->setLineID('DNL-1'));
		$line->setOriginalDocument((new S\OriginalDocumentLine($originalDocument))->setLineID('ODL-1'));
		$line->setContract((new S\ContractLine($contract))->setParagraphID('PAR-1'));

		$line->setEgovClassifier('00000099');
		$line->setInvoicedQuantity($this->quantityViaSetters('ks', '1'));
		$line->setLineExtensionAmountCurr('3.92');
		$line->setLineExtensionAmountBeforeDiscount('110.0');
		$line->setLineExtensionAmountTaxInclusiveCurr('4.75');
		$line->setLineExtensionAmountTaxInclusiveBeforeDiscount('133.1');
		$line->setNote($this->noteViaSetters('Line note.'));
		$line->setVatNote($this->noteViaSetters('§ 47 zákona o DPH'));
		$line->setItem($this->itemViaSetters());

		return $line;
	}

	private function fullClassifiedTaxCategoryViaSetters(): S\ClassifiedTaxCategory
	{
		$category = new S\ClassifiedTaxCategory('21', S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP);
		$category->setVatApplicable(true);
		$localReverseCharge = new S\LocalReverseCharge(S\LocalReverseCharge::LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_GOLD);
		$localReverseCharge->setLocalReverseChargeQuantity($this->quantityViaSetters('g', '5'));
		$category->setLocalReverseCharge($localReverseCharge);
		return $category;
	}

	private function fullTaxCategoryViaSetters(): S\TaxCategory
	{
		$category = new S\TaxCategory('21');
		$category->setTaxScheme('VAT');
		$category->setVatApplicable(true);
		$category->setLocalReverseChargeFlag(true);
		return $category;
	}

	private function itemViaSetters(): S\Item
	{
		$item = new S\Item;
		$item->setDescription('Premium widget');
		$item->setCatalogueItemIdentification(new S\CatalogueItemIdentification('CAT-1'));
		$item->setSellersItemIdentification(new S\SellersItemIdentification('SELLER-1'));
		$item->setSecondarySellersItemIdentification(new S\SecondarySellersItemIdentification('SELLER-2'));
		$item->setTertiarySellersItemIdentification(new S\TertiarySellersItemIdentification('SELLER-3'));
		$item->setBuyersItemIdentification(new S\BuyersItemIdentification('BUYER-1'));

		$storeBatch = new S\StoreBatch('BATCH-1', $this->quantityViaSetters('ks', '10'), S\StoreBatch::BATCH_OR_SERIAL_NUMBER_BATCH);
		$storeBatch->setExpirationDate($this->date('2025-12-31'));
		$storeBatch->setSpecification('Cold storage');
		$storeBatch->setSealSeriesID('SEAL-1');
		$storeBatch->setNote($this->noteViaSetters('Handle with care.'));
		$item->setStoreBatches((new S\StoreBatches)->add($storeBatch));

		return $item;
	}

	private function detailsViaSetters(): S\Details
	{
		$details = new S\Details;
		$details->setDocumentID('PAY-1');
		$details->setIssueDate($this->date('2021-08-16'));
		$details->setPaymentDueDate($this->date('2021-08-30'));
		$details->setId('123456789');
		$details->setBankCode('0800');
		$details->setName('Česká spořitelna, a. s.');
		$details->setIban('CZ6508000000192000145399');
		$details->setBic('GIBACZPX');
		$details->setVariableSymbol('20210001');
		$details->setConstantSymbol('0308');
		$details->setSpecificSymbol('12345');
		return $details;
	}

	private function alternateBankAccountsViaSetters(): S\AlternateBankAccounts
	{
		$account = new S\AlternateBankAccount;
		$account->setId('987654321');
		$account->setBankCode('0100');
		$account->setName('Komerční banka, a. s.');
		$account->setIban('CZ6501000000000987654321');
		$account->setBic('KOMBCZPP');
		return (new S\AlternateBankAccounts)->add($account);
	}

	private function fullPartyViaSetters(string $id, string $name): S\Party
	{
		$party = $this->buildBasicParty($id, $name);

		$party->getPartyIdentification()->setCatalogFirmIdentification('CAT-FIRM-' . $id);
		$party->getPartyIdentification()->setUserID('USER-' . $id);

		$schemes = new S\PartyTaxSchemes;
		$schemes->add(new S\PartyTaxScheme('CZ' . $id, 'VAT'));
		$schemes->add(new S\PartyTaxScheme('SK' . $id, 'TIN'));
		$party->setPartyTaxSchemes($schemes);

		$register = new S\RegisterIdentification;
		$register->setRegisterKeptAt('Městský soud v Praze');
		$register->setRegisterFileRef('B 1234');
		$register->setRegisterDate($this->date('2001-03-14'));
		$register->setPreformatted('Spisová značka B 1234 vedená u Městského soudu v Praze');
		$party->setRegisterIdentification($register);

		$contact = new S\Contact;
		$contact->setName('Jan Novák');
		$contact->setTelephone('+420123456789');
		$contact->setElectronicMail('jan.novak@example.com');
		$party->setContact($contact);

		return $party;
	}

	private function noteViaSetters(string $content, ?string $languageID = null): S\Note
	{
		$note = new S\Note;
		$note->setContent($content);

		if ($languageID !== null) {
			$note->setLanguageID($languageID);
		}

		return $note;
	}

	private function quantityViaSetters(string $unitCode, string $content): S\Quantity
	{
		$quantity = new S\Quantity;
		$quantity->setUnitCode($unitCode);
		$quantity->setContent($content);
		return $quantity;
	}

	// ---------------------------------------------------------------------------------------------
	// Building the same graph through magic properties.
	// ---------------------------------------------------------------------------------------------

	private function buildViaProperties(): ISDOC\Invoice
	{
		$invoice = new ISDOC\Invoice(
			'2021-0001',
			'00000000-0000-0000-0000-000000001234',
			$this->date('2021-08-16'),
			true,
			'CZK',
			new S\AccountingSupplierParty($this->fullPartyViaProperties('12345678', 'Dodavatel, a. s.')),
		);

		$invoice->documentType              = S::DOCUMENT_TYPE_INVOICE;
		$invoice->subDocumentType           = 'TAX';
		$invoice->subDocumentTypeOrigin     = 'CZ-GFR';
		$invoice->targetConsolidator        = '0800';
		$invoice->clientOnTargetConsolidator = 'CLIENT-1';
		$invoice->clientBankAccount         = '123456789/0800';
		$invoice->egovFlag                  = true;
		$invoice->isds_id                   = 'abcdefgh';
		$invoice->file                      = 'FILE-2021-0001';
		$invoice->referenceNumber           = 'REF-2021-0001';
		$invoice->issuingSystem             = 'Acme Billing 1.0';
		$invoice->taxPointDate              = $this->date('2021-08-15');
		$invoice->foreignCurrencyCode       = 'EUR';
		$invoice->currRate                  = '25.5';
		$invoice->refCurrRate               = '1.0';
		$invoice->version                   = ISDOC\Invoice::VERSION;

		$invoice->electronicPossibilityAgreement->content    = 'Agreed electronically.';
		$invoice->electronicPossibilityAgreement->languageID = 'cs';
		$invoice->note = $this->noteViaProperties('Thank you for your business.', 'en');

		$invoice->sellerSupplierParty     = new S\SellerSupplierParty($this->buildBasicParty('11111111', 'Prodejce, a. s.'));
		$invoice->accountingCustomerParty = new S\AccountingCustomerParty($this->fullPartyViaProperties('87654321', 'Odběratel, a. s.'));
		$invoice->buyerCustomerParty      = new S\BuyerCustomerParty($this->buildBasicParty('22222222', 'Kupující, a. s.'));
		$invoice->delivery                = new S\Delivery($this->buildBasicParty('33333333', 'Místo dodání, a. s.'));

		$anonymous = new S\AnonymousCustomerParty('ANON-1');
		$anonymous->idScheme = 'https://www.rfc-editor.org/rfc/rfc9562.html';
		$invoice->anonymousCustomerParty = $anonymous;

		$order = new S\Order('SO-1');
		$order->externalOrderID        = 'PO-1';
		$order->issueDate              = $this->date('2021-07-01');
		$order->externalOrderIssueDate = $this->date('2021-06-30');
		$order->uuid                   = '00000000-0000-0000-0000-0000000000a1';
		$order->isds_id                = 'order001';
		$order->file                   = 'ORDER-FILE';
		$order->referenceNumber        = 'ORDER-REF';
		$invoice->orderReferences = (new S\OrderReferences)->add($order);

		$deliveryNote = new S\DeliveryNote('DN-1');
		$deliveryNote->issueDate = $this->date('2021-07-10');
		$deliveryNote->uuid      = '00000000-0000-0000-0000-0000000000a2';
		$invoice->deliveryNoteReferences = (new S\DeliveryNoteReferences)->add($deliveryNote);

		$originalDocument = new S\OriginalDocument('OD-1');
		$originalDocument->issueDate = $this->date('2021-05-01');
		$originalDocument->uuid      = '00000000-0000-0000-0000-0000000000a3';
		$invoice->originalDocumentReferences = (new S\OriginalDocumentReferences)->add($originalDocument);

		$contract = new S\Contract('CT-1', $this->date('2021-01-01'));
		$contract->uuid            = '00000000-0000-0000-0000-0000000000a4';
		$contract->lastValidDate   = $this->date('2022-01-01');
		$contract->isds_id         = 'contract1';
		$contract->file            = 'CONTRACT-FILE';
		$contract->referenceNumber = 'CONTRACT-REF';
		$invoice->contractReferences = (new S\ContractReferences)->add($contract);

		$invoice->invoiceLines->add($this->richLineViaProperties($order, $deliveryNote, $originalDocument, $contract));
		$invoice->invoiceLines->add(new S\InvoiceLine(
			'2',
			'250.0',
			'302.5',
			'52.5',
			'250.0',
			'302.5',
			new S\ClassifiedTaxCategory('21', S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_BOTTOM),
		));

		$nonTaxedDeposit = new S\NonTaxedDeposit('NTD-1', '555', '100.0');
		$nonTaxedDeposit->depositAmountCurr = '4.0';
		$invoice->nonTaxedDeposits = (new S\NonTaxedDeposits)->add($nonTaxedDeposit);

		$taxedDeposit = new S\TaxedDeposit(
			'TD-1',
			'556',
			'200.0',
			'242.0',
			new S\ClassifiedTaxCategory('21', S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP),
		);
		$taxedDeposit->taxableDepositAmountCurr      = '8.0';
		$taxedDeposit->taxInclusiveDepositAmountCurr = '9.68';
		$invoice->taxedDeposits = (new S\TaxedDeposits)->add($taxedDeposit);

		$taxTotal = $invoice->taxTotal;
		$taxTotal->taxAmount     = '73.5';
		$taxTotal->taxAmountCurr = '2.88';
		$taxSubTotal = new S\TaxSubTotal(
			'350.0', '73.5', '423.5', '0.0', '0.0', '0.0', '0.0', '0.0', '0.0', $this->fullTaxCategoryViaProperties(),
		);
		$taxSubTotal->taxableAmountCurr                = '13.72';
		$taxSubTotal->taxAmountCurr                    = '2.88';
		$taxSubTotal->taxInclusiveAmountCurr           = '16.6';
		$taxSubTotal->alreadyClaimedTaxableAmountCurr  = '0.0';
		$taxSubTotal->alreadyClaimedTaxAmountCurr      = '0.0';
		$taxSubTotal->alreadyClaimedTaxInclusiveAmountCurr = '0.0';
		$taxSubTotal->differenceTaxableAmountCurr      = '0.0';
		$taxSubTotal->differenceTaxAmountCurr          = '0.0';
		$taxSubTotal->differenceTaxInclusiveAmountCurr = '0.0';
		$taxTotal->add($taxSubTotal);

		$total = $invoice->legalMonetaryTotal;
		$total->taxExclusiveAmount                  = '350.0';
		$total->taxExclusiveAmountCurr              = '13.72';
		$total->taxInclusiveAmount                  = '423.5';
		$total->taxInclusiveAmountCurr              = '16.6';
		$total->alreadyClaimedTaxExclusiveAmount    = '0.0';
		$total->alreadyClaimedTaxExclusiveAmountCurr = '0.0';
		$total->alreadyClaimedTaxInclusiveAmount    = '0.0';
		$total->alreadyClaimedTaxInclusiveAmountCurr = '0.0';
		$total->differenceTaxExclusiveAmount        = '0.0';
		$total->differenceTaxExclusiveAmountCurr    = '0.0';
		$total->differenceTaxInclusiveAmount        = '0.0';
		$total->differenceTaxInclusiveAmountCurr    = '0.0';
		$total->paidDepositsAmount                  = '0.0';
		$total->paidDepositsAmountCurr              = '0.0';
		$total->payableRoundingAmount               = '0.5';
		$total->payableRoundingAmountCurr           = '0.02';
		$total->payableAmount                       = '424.0';
		$total->payableAmountCurr                   = '16.62';

		$payment = new S\Payment('424.0', S\Payment::PAYMENT_MEANS_CODE_CREDIT_TRANSFER);
		$payment->partialPayment = true;
		$payment->details        = $this->detailsViaProperties();
		$paymentMeans = (new S\PaymentMeans)->add($payment);
		$paymentMeans->alternateBankAccounts = $this->alternateBankAccountsViaProperties();
		$invoice->paymentMeans = $paymentMeans;

		$supplement = new S\Supplement(
			'invoice.pdf',
			new S\DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
			'2jmj7l5rSw0yVb/vlWAYkK/YBwk=',
		);
		$supplement->preview = true;
		$invoice->supplementsList = (new S\SupplementsList)->add($supplement);

		return $invoice;
	}

	private function richLineViaProperties(
		S\Order $order,
		S\DeliveryNote $deliveryNote,
		S\OriginalDocument $originalDocument,
		S\Contract $contract,
	): S\InvoiceLine
	{
		$line = new S\InvoiceLine('1', '100.0', '121.0', '21.0', '100.0', '121.0', $this->fullClassifiedTaxCategoryViaProperties());

		$orderLine = new S\OrderLine($order);
		$orderLine->lineID = 'OL-1';
		$line->order = $orderLine;

		$deliveryNoteLine = new S\DeliveryNoteLine($deliveryNote);
		$deliveryNoteLine->lineID = 'DNL-1';
		$line->deliveryNote = $deliveryNoteLine;

		$originalDocumentLine = new S\OriginalDocumentLine($originalDocument);
		$originalDocumentLine->lineID = 'ODL-1';
		$line->originalDocument = $originalDocumentLine;

		$contractLine = new S\ContractLine($contract);
		$contractLine->paragraphID = 'PAR-1';
		$line->contract = $contractLine;

		$line->egovClassifier                                = '00000099';
		$line->invoicedQuantity                              = $this->quantityViaProperties('ks', '1');
		$line->lineExtensionAmountCurr                       = '3.92';
		$line->lineExtensionAmountBeforeDiscount             = '110.0';
		$line->lineExtensionAmountTaxInclusiveCurr           = '4.75';
		$line->lineExtensionAmountTaxInclusiveBeforeDiscount = '133.1';
		$line->note    = $this->noteViaProperties('Line note.');
		$line->vatNote = $this->noteViaProperties('§ 47 zákona o DPH');
		$line->item    = $this->itemViaProperties();

		return $line;
	}

	private function fullClassifiedTaxCategoryViaProperties(): S\ClassifiedTaxCategory
	{
		$category = new S\ClassifiedTaxCategory('21', S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP);
		$category->vatApplicable = true;
		$localReverseCharge = new S\LocalReverseCharge(S\LocalReverseCharge::LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_GOLD);
		$localReverseCharge->localReverseChargeQuantity = $this->quantityViaProperties('g', '5');
		$category->localReverseCharge = $localReverseCharge;
		return $category;
	}

	private function fullTaxCategoryViaProperties(): S\TaxCategory
	{
		$category = new S\TaxCategory('21');
		$category->taxScheme              = 'VAT';
		$category->vatApplicable          = true;
		$category->localReverseChargeFlag = true;
		return $category;
	}

	private function itemViaProperties(): S\Item
	{
		$item = new S\Item;
		$item->description                        = 'Premium widget';
		$item->catalogueItemIdentification        = new S\CatalogueItemIdentification('CAT-1');
		$item->sellersItemIdentification          = new S\SellersItemIdentification('SELLER-1');
		$item->secondarySellersItemIdentification = new S\SecondarySellersItemIdentification('SELLER-2');
		$item->tertiarySellersItemIdentification  = new S\TertiarySellersItemIdentification('SELLER-3');
		$item->buyersItemIdentification           = new S\BuyersItemIdentification('BUYER-1');

		$storeBatch = new S\StoreBatch('BATCH-1', $this->quantityViaProperties('ks', '10'), S\StoreBatch::BATCH_OR_SERIAL_NUMBER_BATCH);
		$storeBatch->expirationDate = $this->date('2025-12-31');
		$storeBatch->specification  = 'Cold storage';
		$storeBatch->sealSeriesID   = 'SEAL-1';
		$storeBatch->note           = $this->noteViaProperties('Handle with care.');
		$item->storeBatches = (new S\StoreBatches)->add($storeBatch);

		return $item;
	}

	private function detailsViaProperties(): S\Details
	{
		$details = new S\Details;
		$details->documentID     = 'PAY-1';
		$details->issueDate      = $this->date('2021-08-16');
		$details->paymentDueDate = $this->date('2021-08-30');
		$details->id             = '123456789';
		$details->bankCode       = '0800';
		$details->name           = 'Česká spořitelna, a. s.';
		$details->iban           = 'CZ6508000000192000145399';
		$details->bic            = 'GIBACZPX';
		$details->variableSymbol = '20210001';
		$details->constantSymbol = '0308';
		$details->specificSymbol = '12345';
		return $details;
	}

	private function alternateBankAccountsViaProperties(): S\AlternateBankAccounts
	{
		$account = new S\AlternateBankAccount;
		$account->id       = '987654321';
		$account->bankCode = '0100';
		$account->name     = 'Komerční banka, a. s.';
		$account->iban     = 'CZ6501000000000987654321';
		$account->bic      = 'KOMBCZPP';
		return (new S\AlternateBankAccounts)->add($account);
	}

	private function fullPartyViaProperties(string $id, string $name): S\Party
	{
		$party = $this->buildBasicParty($id, $name);

		$party->partyIdentification->catalogFirmIdentification = 'CAT-FIRM-' . $id;
		$party->partyIdentification->userID                    = 'USER-' . $id;

		$schemes = new S\PartyTaxSchemes;
		$schemes->add(new S\PartyTaxScheme('CZ' . $id, 'VAT'));
		$schemes->add(new S\PartyTaxScheme('SK' . $id, 'TIN'));
		// partyTaxSchemes has no magic property (only the deprecated singular partyTaxScheme), so it is
		// assigned through its method even in property mode.
		$party->setPartyTaxSchemes($schemes);

		$register = new S\RegisterIdentification;
		$register->registerKeptAt  = 'Městský soud v Praze';
		$register->registerFileRef = 'B 1234';
		$register->registerDate    = $this->date('2001-03-14');
		$register->preformatted    = 'Spisová značka B 1234 vedená u Městského soudu v Praze';
		$party->registerIdentification = $register;

		$contact = new S\Contact;
		$contact->name           = 'Jan Novák';
		$contact->telephone      = '+420123456789';
		$contact->electronicMail = 'jan.novak@example.com';
		$party->contact = $contact;

		return $party;
	}

	private function noteViaProperties(string $content, ?string $languageID = null): S\Note
	{
		$note = new S\Note;
		$note->content = $content;

		if ($languageID !== null) {
			$note->languageID = $languageID;
		}

		return $note;
	}

	private function quantityViaProperties(string $unitCode, string $content): S\Quantity
	{
		$quantity = new S\Quantity;
		$quantity->unitCode = $unitCode;
		$quantity->content  = $content;
		return $quantity;
	}

	private function buildBasicParty(string $id, string $name): S\Party
	{
		return new S\Party(
			new S\PartyIdentification($id),
			new S\PartyName($name),
			new S\PostalAddress('Dlouhá', '1234', 'Praha', '100 01', new S\Country('CZ', 'Česká republika')),
		);
	}

	// ---------------------------------------------------------------------------------------------
	// Reading everything back through get*() methods.
	// ---------------------------------------------------------------------------------------------

	private function assertViaGetters(S $invoice): void
	{
		self::assertSame(S::DOCUMENT_TYPE_INVOICE, $invoice->getDocumentType());
		self::assertSame('2021-0001', $invoice->getId());
		self::assertSame('00000000-0000-0000-0000-000000001234', $invoice->getUuid());
		self::assertSame('2021-08-16', $invoice->getIssueDate()->format('Y-m-d'));
		self::assertTrue($invoice->getVatApplicable());
		self::assertSame('CZK', $invoice->getLocalCurrencyCode());
		self::assertSame('TAX', $invoice->getSubDocumentType());
		self::assertSame('CZ-GFR', $invoice->getSubDocumentTypeOrigin());
		self::assertSame('0800', $invoice->getTargetConsolidator());
		self::assertSame('CLIENT-1', $invoice->getClientOnTargetConsolidator());
		self::assertSame('123456789/0800', $invoice->getClientBankAccount());
		self::assertTrue($invoice->getEgovFlag());
		self::assertSame('abcdefgh', $invoice->getIsds_id());
		self::assertSame('FILE-2021-0001', $invoice->getFile());
		self::assertSame('REF-2021-0001', $invoice->getReferenceNumber());
		self::assertSame('Acme Billing 1.0', $invoice->getIssuingSystem());
		self::assertSame('2021-08-15', $this->notNull($invoice->getTaxPointDate())->format('Y-m-d'));
		self::assertSame('EUR', $invoice->getForeignCurrencyCode());
		self::assertSame('25.5', $invoice->getCurrRate());
		self::assertSame('1.0', $invoice->getRefCurrRate());
		self::assertSame(ISDOC\Invoice::VERSION, $invoice->getVersion());

		self::assertSame('Agreed electronically.', $invoice->getElectronicPossibilityAgreement()->getContent());
		self::assertSame('cs', $invoice->getElectronicPossibilityAgreement()->getLanguageID());
		$note = $this->notNull($invoice->getNote());
		self::assertSame('Thank you for your business.', $note->getContent());
		self::assertSame('en', $note->getLanguageID());

		$this->assertFullPartyViaGetters($invoice->getAccountingSupplierParty()->getParty(), '12345678', 'Dodavatel, a. s.');
		$this->assertFullPartyViaGetters($this->notNull($invoice->getAccountingCustomerParty())->getParty(), '87654321', 'Odběratel, a. s.');
		self::assertSame('11111111', $this->notNull($invoice->getSellerSupplierParty())->getParty()->getPartyIdentification()->getId());
		self::assertSame('22222222', $this->notNull($invoice->getBuyerCustomerParty())->getParty()->getPartyIdentification()->getId());
		self::assertSame('33333333', $this->notNull($invoice->getDelivery())->getParty()->getPartyIdentification()->getId());

		$anonymous = $this->notNull($invoice->getAnonymousCustomerParty());
		self::assertSame('ANON-1', $anonymous->getId());
		self::assertSame('https://www.rfc-editor.org/rfc/rfc9562.html', $anonymous->getIdScheme());

		$order = $this->first($this->notNull($invoice->getOrderReferences()));
		self::assertSame('SO-1', $order->getSalesOrderID());
		self::assertSame('PO-1', $order->getExternalOrderID());
		self::assertSame('2021-07-01', $this->notNull($order->getIssueDate())->format('Y-m-d'));
		self::assertSame('2021-06-30', $this->notNull($order->getExternalOrderIssueDate())->format('Y-m-d'));
		self::assertSame('00000000-0000-0000-0000-0000000000a1', $order->getUuid());
		self::assertSame('order001', $order->getIsds_id());
		self::assertSame('ORDER-FILE', $order->getFile());
		self::assertSame('ORDER-REF', $order->getReferenceNumber());

		$deliveryNote = $this->first($this->notNull($invoice->getDeliveryNoteReferences()));
		self::assertSame('DN-1', $deliveryNote->getId());
		self::assertSame('2021-07-10', $this->notNull($deliveryNote->getIssueDate())->format('Y-m-d'));
		self::assertSame('00000000-0000-0000-0000-0000000000a2', $deliveryNote->getUuid());

		$originalDocument = $this->first($this->notNull($invoice->getOriginalDocumentReferences()));
		self::assertSame('OD-1', $originalDocument->getId());
		self::assertSame('2021-05-01', $this->notNull($originalDocument->getIssueDate())->format('Y-m-d'));
		self::assertSame('00000000-0000-0000-0000-0000000000a3', $originalDocument->getUuid());

		$contract = $this->first($this->notNull($invoice->getContractReferences()));
		self::assertSame('CT-1', $contract->getId());
		self::assertSame('00000000-0000-0000-0000-0000000000a4', $contract->getUuid());
		self::assertSame('2021-01-01', $contract->getIssueDate()->format('Y-m-d'));
		self::assertSame('2022-01-01', $this->notNull($contract->getLastValidDate())->format('Y-m-d'));
		self::assertSame('contract1', $contract->getIsds_id());
		self::assertSame('CONTRACT-FILE', $contract->getFile());
		self::assertSame('CONTRACT-REF', $contract->getReferenceNumber());

		self::assertCount(2, $invoice->getInvoiceLines());
		$line = $this->first($invoice->getInvoiceLines());
		self::assertSame('1', $line->getId());
		self::assertSame('100.0', $line->getLineExtensionAmount());
		self::assertSame('121.0', $line->getLineExtensionAmountTaxInclusive());
		self::assertSame('21.0', $line->getLineExtensionTaxAmount());
		self::assertSame('100.0', $line->getUnitPrice());
		self::assertSame('121.0', $line->getUnitPriceTaxInclusive());
		self::assertSame('00000099', $line->getEgovClassifier());
		self::assertSame('3.92', $line->getLineExtensionAmountCurr());
		self::assertSame('110.0', $line->getLineExtensionAmountBeforeDiscount());
		self::assertSame('4.75', $line->getLineExtensionAmountTaxInclusiveCurr());
		self::assertSame('133.1', $line->getLineExtensionAmountTaxInclusiveBeforeDiscount());

		$category = $line->getClassifiedTaxCategory();
		self::assertSame('21', $category->getPercent());
		self::assertSame(S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP, $category->getVatCalculationMethod());
		self::assertTrue($category->getVatApplicable());
		$localReverseCharge = $this->notNull($category->getLocalReverseCharge());
		self::assertSame(S\LocalReverseCharge::LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_GOLD, $localReverseCharge->getLocalReverseChargeCode());
		$localReverseChargeQuantity = $this->notNull($localReverseCharge->getLocalReverseChargeQuantity());
		self::assertSame('g', $localReverseChargeQuantity->getUnitCode());
		self::assertSame('5', $localReverseChargeQuantity->getContent());

		$invoicedQuantity = $this->notNull($line->getInvoicedQuantity());
		self::assertSame('ks', $invoicedQuantity->getUnitCode());
		self::assertSame('1', $invoicedQuantity->getContent());

		$orderLine = $this->notNull($line->getOrder());
		self::assertSame('OL-1', $orderLine->getLineID());
		self::assertSame('SO-1', $orderLine->getOrder()->getSalesOrderID());
		$deliveryNoteLine = $this->notNull($line->getDeliveryNote());
		self::assertSame('DNL-1', $deliveryNoteLine->getLineID());
		self::assertSame('DN-1', $deliveryNoteLine->getDeliveryNote()->getId());
		$originalDocumentLine = $this->notNull($line->getOriginalDocument());
		self::assertSame('ODL-1', $originalDocumentLine->getLineID());
		self::assertSame('OD-1', $originalDocumentLine->getOriginalDocument()->getId());
		$contractLine = $this->notNull($line->getContract());
		self::assertSame('PAR-1', $contractLine->getParagraphID());
		self::assertSame('CT-1', $contractLine->getContract()->getId());

		self::assertSame('Line note.', $this->notNull($line->getNote())->getContent());
		self::assertSame('§ 47 zákona o DPH', $this->notNull($line->getVatNote())->getContent());

		$item = $this->notNull($line->getItem());
		self::assertSame('Premium widget', $item->getDescription());
		self::assertSame('CAT-1', $this->notNull($item->getCatalogueItemIdentification())->getId());
		self::assertSame('SELLER-1', $this->notNull($item->getSellersItemIdentification())->getId());
		self::assertSame('SELLER-2', $this->notNull($item->getSecondarySellersItemIdentification())->getId());
		self::assertSame('SELLER-3', $this->notNull($item->getTertiarySellersItemIdentification())->getId());
		self::assertSame('BUYER-1', $this->notNull($item->getBuyersItemIdentification())->getId());
		$storeBatch = $this->first($this->notNull($item->getStoreBatches()));
		self::assertSame('BATCH-1', $storeBatch->getName());
		self::assertSame(S\StoreBatch::BATCH_OR_SERIAL_NUMBER_BATCH, $storeBatch->getBatchOrSerialNumber());
		self::assertSame('ks', $storeBatch->getQuantity()->getUnitCode());
		self::assertSame('10', $storeBatch->getQuantity()->getContent());
		self::assertSame('2025-12-31', $this->notNull($storeBatch->getExpirationDate())->format('Y-m-d'));
		self::assertSame('Cold storage', $storeBatch->getSpecification());
		self::assertSame('SEAL-1', $storeBatch->getSealSeriesID());
		self::assertSame('Handle with care.', $this->notNull($storeBatch->getNote())->getContent());

		$nonTaxedDeposit = $this->first($this->notNull($invoice->getNonTaxedDeposits()));
		self::assertSame('NTD-1', $nonTaxedDeposit->getId());
		self::assertSame('555', $nonTaxedDeposit->getVariableSymbol());
		self::assertSame('100.0', $nonTaxedDeposit->getDepositAmount());
		self::assertSame('4.0', $nonTaxedDeposit->getDepositAmountCurr());

		$taxedDeposit = $this->first($this->notNull($invoice->getTaxedDeposits()));
		self::assertSame('TD-1', $taxedDeposit->getId());
		self::assertSame('556', $taxedDeposit->getVariableSymbol());
		self::assertSame('200.0', $taxedDeposit->getTaxableDepositAmount());
		self::assertSame('242.0', $taxedDeposit->getTaxInclusiveDepositAmount());
		self::assertSame('8.0', $taxedDeposit->getTaxableDepositAmountCurr());
		self::assertSame('9.68', $taxedDeposit->getTaxInclusiveDepositAmountCurr());
		self::assertSame('21', $taxedDeposit->getClassifiedTaxCategory()->getPercent());

		$taxTotal = $invoice->getTaxTotal();
		self::assertSame('73.5', $taxTotal->getTaxAmount());
		self::assertSame('2.88', $taxTotal->getTaxAmountCurr());
		$taxSubTotal = $this->first($taxTotal);
		self::assertSame('350.0', $taxSubTotal->getTaxableAmount());
		self::assertSame('73.5', $taxSubTotal->getTaxAmount());
		self::assertSame('423.5', $taxSubTotal->getTaxInclusiveAmount());
		self::assertSame('13.72', $taxSubTotal->getTaxableAmountCurr());
		self::assertSame('16.6', $taxSubTotal->getTaxInclusiveAmountCurr());
		$taxCategory = $taxSubTotal->getTaxCategory();
		self::assertSame('21', $taxCategory->getPercent());
		self::assertSame('VAT', $taxCategory->getTaxScheme());
		self::assertTrue($taxCategory->getVatApplicable());
		self::assertTrue($taxCategory->getLocalReverseChargeFlag());

		$total = $invoice->getLegalMonetaryTotal();
		self::assertSame('350.0', $total->getTaxExclusiveAmount());
		self::assertSame('13.72', $total->getTaxExclusiveAmountCurr());
		self::assertSame('423.5', $total->getTaxInclusiveAmount());
		self::assertSame('16.6', $total->getTaxInclusiveAmountCurr());
		self::assertSame('0.0', $total->getAlreadyClaimedTaxExclusiveAmount());
		self::assertSame('0.0', $total->getDifferenceTaxExclusiveAmount());
		self::assertSame('0.0', $total->getPaidDepositsAmount());
		self::assertSame('0.5', $total->getPayableRoundingAmount());
		self::assertSame('0.02', $total->getPayableRoundingAmountCurr());
		self::assertSame('424.0', $total->getPayableAmount());
		self::assertSame('16.62', $total->getPayableAmountCurr());

		$payment = $this->first($this->notNull($invoice->getPaymentMeans()));
		self::assertSame('424.0', $payment->getPaidAmount());
		self::assertSame(S\Payment::PAYMENT_MEANS_CODE_CREDIT_TRANSFER, $payment->getPaymentMeansCode());
		self::assertTrue($payment->getPartialPayment());
		$details = $this->notNull($payment->getDetails());
		self::assertSame('PAY-1', $details->getDocumentID());
		self::assertSame('2021-08-16', $this->notNull($details->getIssueDate())->format('Y-m-d'));
		self::assertSame('2021-08-30', $this->notNull($details->getPaymentDueDate())->format('Y-m-d'));
		self::assertSame('123456789', $details->getId());
		self::assertSame('0800', $details->getBankCode());
		self::assertSame('Česká spořitelna, a. s.', $details->getName());
		self::assertSame('CZ6508000000192000145399', $details->getIban());
		self::assertSame('GIBACZPX', $details->getBic());
		self::assertSame('20210001', $details->getVariableSymbol());
		self::assertSame('0308', $details->getConstantSymbol());
		self::assertSame('12345', $details->getSpecificSymbol());
		$account = $this->first($this->notNull($this->notNull($invoice->getPaymentMeans())->getAlternateBankAccounts()));
		self::assertSame('987654321', $account->getId());
		self::assertSame('0100', $account->getBankCode());
		self::assertSame('Komerční banka, a. s.', $account->getName());
		self::assertSame('CZ6501000000000987654321', $account->getIban());
		self::assertSame('KOMBCZPP', $account->getBic());

		$supplement = $this->first($this->notNull($invoice->getSupplementsList()));
		self::assertSame('invoice.pdf', $supplement->getFilename());
		self::assertSame('http://www.w3.org/2000/09/xmldsig#sha1', $supplement->getDigestMethod()->getAlgorithm());
		self::assertSame('2jmj7l5rSw0yVb/vlWAYkK/YBwk=', $supplement->getDigestValue());
		self::assertTrue($supplement->getPreview());
	}

	private function assertFullPartyViaGetters(S\Party $party, string $id, string $name): void
	{
		self::assertSame($id, $party->getPartyIdentification()->getId());
		self::assertSame('CAT-FIRM-' . $id, $party->getPartyIdentification()->getCatalogFirmIdentification());
		self::assertSame('USER-' . $id, $party->getPartyIdentification()->getUserID());
		self::assertSame($name, $party->getPartyName()->getName());

		$address = $party->getPostalAddress();
		self::assertSame('Dlouhá', $address->getStreetName());
		self::assertSame('1234', $address->getBuildingNumber());
		self::assertSame('Praha', $address->getCityName());
		self::assertSame('100 01', $address->getPostalZone());
		self::assertSame('CZ', $address->getCountry()->getIdentificationCode());
		self::assertSame('Česká republika', $address->getCountry()->getName());

		$schemes = $this->notNull($party->getPartyTaxSchemes());
		self::assertCount(2, $schemes);
		$scheme = $this->first($schemes);
		self::assertSame('CZ' . $id, $scheme->getCompanyID());
		self::assertSame('VAT', $scheme->getTaxScheme());

		$register = $this->notNull($party->getRegisterIdentification());
		self::assertSame('Městský soud v Praze', $register->getRegisterKeptAt());
		self::assertSame('B 1234', $register->getRegisterFileRef());
		self::assertSame('2001-03-14', $this->notNull($register->getRegisterDate())->format('Y-m-d'));
		self::assertSame('Spisová značka B 1234 vedená u Městského soudu v Praze', $register->getPreformatted());

		$contact = $this->notNull($party->getContact());
		self::assertSame('Jan Novák', $contact->getName());
		self::assertSame('+420123456789', $contact->getTelephone());
		self::assertSame('jan.novak@example.com', $contact->getElectronicMail());
	}

	// ---------------------------------------------------------------------------------------------
	// Reading everything back through magic properties.
	// ---------------------------------------------------------------------------------------------

	private function assertViaProperties(S $invoice): void
	{
		self::assertSame(S::DOCUMENT_TYPE_INVOICE, $invoice->documentType);
		self::assertSame('2021-0001', $invoice->id);
		self::assertSame('00000000-0000-0000-0000-000000001234', $invoice->uuid);
		self::assertSame('2021-08-16', $invoice->issueDate->format('Y-m-d'));
		self::assertTrue($invoice->vatApplicable);
		self::assertSame('CZK', $invoice->localCurrencyCode);
		self::assertSame('TAX', $invoice->subDocumentType);
		self::assertSame('CZ-GFR', $invoice->subDocumentTypeOrigin);
		self::assertSame('0800', $invoice->targetConsolidator);
		self::assertSame('CLIENT-1', $invoice->clientOnTargetConsolidator);
		self::assertSame('123456789/0800', $invoice->clientBankAccount);
		self::assertTrue($invoice->egovFlag);
		self::assertSame('abcdefgh', $invoice->isds_id);
		self::assertSame('FILE-2021-0001', $invoice->file);
		self::assertSame('REF-2021-0001', $invoice->referenceNumber);
		self::assertSame('Acme Billing 1.0', $invoice->issuingSystem);
		self::assertSame('2021-08-15', $this->notNull($invoice->taxPointDate)->format('Y-m-d'));
		self::assertSame('EUR', $invoice->foreignCurrencyCode);
		self::assertSame('25.5', $invoice->currRate);
		self::assertSame('1.0', $invoice->refCurrRate);
		self::assertSame(ISDOC\Invoice::VERSION, $invoice->version);

		self::assertSame('Agreed electronically.', $invoice->electronicPossibilityAgreement->content);
		self::assertSame('cs', $invoice->electronicPossibilityAgreement->languageID);
		$note = $this->notNull($invoice->note);
		self::assertSame('Thank you for your business.', $note->content);
		self::assertSame('en', $note->languageID);

		$this->assertFullPartyViaProperties($invoice->accountingSupplierParty->party, '12345678', 'Dodavatel, a. s.');
		$this->assertFullPartyViaProperties($this->notNull($invoice->accountingCustomerParty)->party, '87654321', 'Odběratel, a. s.');
		self::assertSame('11111111', $this->notNull($invoice->sellerSupplierParty)->party->partyIdentification->id);
		self::assertSame('22222222', $this->notNull($invoice->buyerCustomerParty)->party->partyIdentification->id);
		self::assertSame('33333333', $this->notNull($invoice->delivery)->party->partyIdentification->id);

		$anonymous = $this->notNull($invoice->anonymousCustomerParty);
		self::assertSame('ANON-1', $anonymous->id);
		self::assertSame('https://www.rfc-editor.org/rfc/rfc9562.html', $anonymous->idScheme);

		$order = $this->first($this->notNull($invoice->orderReferences));
		self::assertSame('SO-1', $order->salesOrderID);
		self::assertSame('PO-1', $order->externalOrderID);
		self::assertSame('2021-07-01', $this->notNull($order->issueDate)->format('Y-m-d'));
		self::assertSame('2021-06-30', $this->notNull($order->externalOrderIssueDate)->format('Y-m-d'));
		self::assertSame('00000000-0000-0000-0000-0000000000a1', $order->uuid);
		self::assertSame('order001', $order->isds_id);
		self::assertSame('ORDER-FILE', $order->file);
		self::assertSame('ORDER-REF', $order->referenceNumber);

		$deliveryNote = $this->first($this->notNull($invoice->deliveryNoteReferences));
		self::assertSame('DN-1', $deliveryNote->id);
		self::assertSame('2021-07-10', $this->notNull($deliveryNote->issueDate)->format('Y-m-d'));
		self::assertSame('00000000-0000-0000-0000-0000000000a2', $deliveryNote->uuid);

		$originalDocument = $this->first($this->notNull($invoice->originalDocumentReferences));
		self::assertSame('OD-1', $originalDocument->id);
		self::assertSame('2021-05-01', $this->notNull($originalDocument->issueDate)->format('Y-m-d'));
		self::assertSame('00000000-0000-0000-0000-0000000000a3', $originalDocument->uuid);

		$contract = $this->first($this->notNull($invoice->contractReferences));
		self::assertSame('CT-1', $contract->id);
		self::assertSame('00000000-0000-0000-0000-0000000000a4', $contract->uuid);
		self::assertSame('2021-01-01', $contract->issueDate->format('Y-m-d'));
		self::assertSame('2022-01-01', $this->notNull($contract->lastValidDate)->format('Y-m-d'));
		self::assertSame('contract1', $contract->isds_id);
		self::assertSame('CONTRACT-FILE', $contract->file);
		self::assertSame('CONTRACT-REF', $contract->referenceNumber);

		self::assertCount(2, $invoice->invoiceLines);
		$line = $this->first($invoice->invoiceLines);
		self::assertSame('1', $line->id);
		self::assertSame('100.0', $line->lineExtensionAmount);
		self::assertSame('121.0', $line->lineExtensionAmountTaxInclusive);
		self::assertSame('21.0', $line->lineExtensionTaxAmount);
		self::assertSame('100.0', $line->unitPrice);
		self::assertSame('121.0', $line->unitPriceTaxInclusive);
		self::assertSame('00000099', $line->egovClassifier);
		self::assertSame('3.92', $line->lineExtensionAmountCurr);
		self::assertSame('110.0', $line->lineExtensionAmountBeforeDiscount);
		self::assertSame('4.75', $line->lineExtensionAmountTaxInclusiveCurr);
		self::assertSame('133.1', $line->lineExtensionAmountTaxInclusiveBeforeDiscount);

		$category = $line->classifiedTaxCategory;
		self::assertSame('21', $category->percent);
		self::assertSame(S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP, $category->vatCalculationMethod);
		self::assertTrue($category->vatApplicable);
		$localReverseCharge = $this->notNull($category->localReverseCharge);
		self::assertSame(S\LocalReverseCharge::LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_GOLD, $localReverseCharge->localReverseChargeCode);
		$localReverseChargeQuantity = $this->notNull($localReverseCharge->localReverseChargeQuantity);
		self::assertSame('g', $localReverseChargeQuantity->unitCode);
		self::assertSame('5', $localReverseChargeQuantity->content);

		$invoicedQuantity = $this->notNull($line->invoicedQuantity);
		self::assertSame('ks', $invoicedQuantity->unitCode);
		self::assertSame('1', $invoicedQuantity->content);

		$orderLine = $this->notNull($line->order);
		self::assertSame('OL-1', $orderLine->lineID);
		self::assertSame('SO-1', $orderLine->order->salesOrderID);
		$deliveryNoteLine = $this->notNull($line->deliveryNote);
		self::assertSame('DNL-1', $deliveryNoteLine->lineID);
		self::assertSame('DN-1', $deliveryNoteLine->deliveryNote->id);
		$originalDocumentLine = $this->notNull($line->originalDocument);
		self::assertSame('ODL-1', $originalDocumentLine->lineID);
		self::assertSame('OD-1', $originalDocumentLine->originalDocument->id);
		$contractLine = $this->notNull($line->contract);
		self::assertSame('PAR-1', $contractLine->paragraphID);
		self::assertSame('CT-1', $contractLine->contract->id);

		self::assertSame('Line note.', $this->notNull($line->note)->content);
		self::assertSame('§ 47 zákona o DPH', $this->notNull($line->vatNote)->content);

		$item = $this->notNull($line->item);
		self::assertSame('Premium widget', $item->description);
		self::assertSame('CAT-1', $this->notNull($item->catalogueItemIdentification)->id);
		self::assertSame('SELLER-1', $this->notNull($item->sellersItemIdentification)->id);
		self::assertSame('SELLER-2', $this->notNull($item->secondarySellersItemIdentification)->id);
		self::assertSame('SELLER-3', $this->notNull($item->tertiarySellersItemIdentification)->id);
		self::assertSame('BUYER-1', $this->notNull($item->buyersItemIdentification)->id);
		$storeBatch = $this->first($this->notNull($item->storeBatches));
		self::assertSame('BATCH-1', $storeBatch->name);
		self::assertSame(S\StoreBatch::BATCH_OR_SERIAL_NUMBER_BATCH, $storeBatch->batchOrSerialNumber);
		self::assertSame('ks', $storeBatch->quantity->unitCode);
		self::assertSame('10', $storeBatch->quantity->content);
		self::assertSame('2025-12-31', $this->notNull($storeBatch->expirationDate)->format('Y-m-d'));
		self::assertSame('Cold storage', $storeBatch->specification);
		self::assertSame('SEAL-1', $storeBatch->sealSeriesID);
		self::assertSame('Handle with care.', $this->notNull($storeBatch->note)->content);

		$nonTaxedDeposit = $this->first($this->notNull($invoice->nonTaxedDeposits));
		self::assertSame('NTD-1', $nonTaxedDeposit->id);
		self::assertSame('555', $nonTaxedDeposit->variableSymbol);
		self::assertSame('100.0', $nonTaxedDeposit->depositAmount);
		self::assertSame('4.0', $nonTaxedDeposit->depositAmountCurr);

		$taxedDeposit = $this->first($this->notNull($invoice->taxedDeposits));
		self::assertSame('TD-1', $taxedDeposit->id);
		self::assertSame('556', $taxedDeposit->variableSymbol);
		self::assertSame('200.0', $taxedDeposit->taxableDepositAmount);
		self::assertSame('242.0', $taxedDeposit->taxInclusiveDepositAmount);
		self::assertSame('8.0', $taxedDeposit->taxableDepositAmountCurr);
		self::assertSame('9.68', $taxedDeposit->taxInclusiveDepositAmountCurr);
		self::assertSame('21', $taxedDeposit->classifiedTaxCategory->percent);

		$taxTotal = $invoice->taxTotal;
		self::assertSame('73.5', $taxTotal->taxAmount);
		self::assertSame('2.88', $taxTotal->taxAmountCurr);
		$taxSubTotal = $this->first($taxTotal);
		self::assertSame('350.0', $taxSubTotal->taxableAmount);
		self::assertSame('73.5', $taxSubTotal->taxAmount);
		self::assertSame('423.5', $taxSubTotal->taxInclusiveAmount);
		self::assertSame('13.72', $taxSubTotal->taxableAmountCurr);
		self::assertSame('16.6', $taxSubTotal->taxInclusiveAmountCurr);
		$taxCategory = $taxSubTotal->taxCategory;
		self::assertSame('21', $taxCategory->percent);
		self::assertSame('VAT', $taxCategory->taxScheme);
		self::assertTrue($taxCategory->vatApplicable);
		self::assertTrue($taxCategory->localReverseChargeFlag);

		$total = $invoice->legalMonetaryTotal;
		self::assertSame('350.0', $total->taxExclusiveAmount);
		self::assertSame('13.72', $total->taxExclusiveAmountCurr);
		self::assertSame('423.5', $total->taxInclusiveAmount);
		self::assertSame('16.6', $total->taxInclusiveAmountCurr);
		self::assertSame('0.0', $total->alreadyClaimedTaxExclusiveAmount);
		self::assertSame('0.0', $total->differenceTaxExclusiveAmount);
		self::assertSame('0.0', $total->paidDepositsAmount);
		self::assertSame('0.5', $total->payableRoundingAmount);
		self::assertSame('0.02', $total->payableRoundingAmountCurr);
		self::assertSame('424.0', $total->payableAmount);
		self::assertSame('16.62', $total->payableAmountCurr);

		$payment = $this->first($this->notNull($invoice->paymentMeans));
		self::assertSame('424.0', $payment->paidAmount);
		self::assertSame(S\Payment::PAYMENT_MEANS_CODE_CREDIT_TRANSFER, $payment->paymentMeansCode);
		self::assertTrue($payment->partialPayment);
		$details = $this->notNull($payment->details);
		self::assertSame('PAY-1', $details->documentID);
		self::assertSame('2021-08-16', $this->notNull($details->issueDate)->format('Y-m-d'));
		self::assertSame('2021-08-30', $this->notNull($details->paymentDueDate)->format('Y-m-d'));
		self::assertSame('123456789', $details->id);
		self::assertSame('0800', $details->bankCode);
		self::assertSame('Česká spořitelna, a. s.', $details->name);
		self::assertSame('CZ6508000000192000145399', $details->iban);
		self::assertSame('GIBACZPX', $details->bic);
		self::assertSame('20210001', $details->variableSymbol);
		self::assertSame('0308', $details->constantSymbol);
		self::assertSame('12345', $details->specificSymbol);
		$account = $this->first($this->notNull($this->notNull($invoice->paymentMeans)->alternateBankAccounts));
		self::assertSame('987654321', $account->id);
		self::assertSame('0100', $account->bankCode);
		self::assertSame('Komerční banka, a. s.', $account->name);
		self::assertSame('CZ6501000000000987654321', $account->iban);
		self::assertSame('KOMBCZPP', $account->bic);

		$supplement = $this->first($this->notNull($invoice->supplementsList));
		self::assertSame('invoice.pdf', $supplement->filename);
		self::assertSame('http://www.w3.org/2000/09/xmldsig#sha1', $supplement->digestMethod->getAlgorithm());
		self::assertSame('2jmj7l5rSw0yVb/vlWAYkK/YBwk=', $supplement->digestValue);
		self::assertTrue($supplement->preview);
	}

	private function assertFullPartyViaProperties(S\Party $party, string $id, string $name): void
	{
		self::assertSame($id, $party->partyIdentification->id);
		self::assertSame('CAT-FIRM-' . $id, $party->partyIdentification->catalogFirmIdentification);
		self::assertSame('USER-' . $id, $party->partyIdentification->userID);
		self::assertSame($name, $party->partyName->name);

		$address = $party->postalAddress;
		self::assertSame('Dlouhá', $address->streetName);
		self::assertSame('1234', $address->buildingNumber);
		self::assertSame('Praha', $address->cityName);
		self::assertSame('100 01', $address->postalZone);
		self::assertSame('CZ', $address->country->identificationCode);
		self::assertSame('Česká republika', $address->country->name);

		// partyTaxSchemes is read through its method (no magic property — see fullPartyViaProperties()).
		$schemes = $this->notNull($party->getPartyTaxSchemes());
		self::assertCount(2, $schemes);
		$scheme = $this->first($schemes);
		self::assertSame('CZ' . $id, $scheme->companyID);
		self::assertSame('VAT', $scheme->taxScheme);

		$register = $this->notNull($party->registerIdentification);
		self::assertSame('Městský soud v Praze', $register->registerKeptAt);
		self::assertSame('B 1234', $register->registerFileRef);
		self::assertSame('2001-03-14', $this->notNull($register->registerDate)->format('Y-m-d'));
		self::assertSame('Spisová značka B 1234 vedená u Městského soudu v Praze', $register->preformatted);

		$contact = $this->notNull($party->contact);
		self::assertSame('Jan Novák', $contact->name);
		self::assertSame('+420123456789', $contact->telephone);
		self::assertSame('jan.novak@example.com', $contact->electronicMail);
	}

	// ---------------------------------------------------------------------------------------------
	// References and other public-surface guards.
	// ---------------------------------------------------------------------------------------------

	/** Cross-line references must point at the very same document-level instances after decoding. */
	private function assertReferencesResolved(S $invoice): void
	{
		$line = $this->first($invoice->getInvoiceLines());

		self::assertSame($this->first($this->notNull($invoice->getOrderReferences())), $this->notNull($line->getOrder())->getOrder());
		self::assertSame($this->first($this->notNull($invoice->getDeliveryNoteReferences())), $this->notNull($line->getDeliveryNote())->getDeliveryNote());
		self::assertSame($this->first($this->notNull($invoice->getOriginalDocumentReferences())), $this->notNull($line->getOriginalDocument())->getOriginalDocument());
		self::assertSame($this->first($this->notNull($invoice->getContractReferences())), $this->notNull($line->getContract())->getContract());
	}

	/**
	 * The deprecated singular Party tax-scheme accessors are part of the public surface and are
	 * exercised here separately (they emit E_USER_DEPRECATED, suppressed at the source with @).
	 */
	public function testDeprecatedPartyTaxSchemeAPI(): void
	{
		$party  = $this->buildBasicParty('12345678', 'Firma, a. s.');
		$scheme = new S\PartyTaxScheme('CZ12345678', 'VAT');

		$party->setPartyTaxScheme($scheme);
		self::assertSame($scheme, $party->getPartyTaxScheme());
		self::assertCount(1, $this->notNull($party->getPartyTaxSchemes()));

		// Clearing through the deprecated setter resets the collection back to null.
		$party->setPartyTaxScheme(null);
		self::assertNull($party->getPartyTaxScheme());
		self::assertNull($party->getPartyTaxSchemes());

		// A present-but-empty collection still yields null from the deprecated singular getter.
		$party->setPartyTaxSchemes(new S\PartyTaxSchemes);
		self::assertNull($party->getPartyTaxScheme());
	}

	/**
	 * EgovClassifiers is a Collection<string>; the current encoder cannot serialize it, so it is not
	 * part of the round-trip. Its public collection API is pinned here on its own.
	 */
	public function testEgovClassifiersAPI(): void
	{
		$classifiers = new S\EgovClassifiers;
		$classifiers->add('00000001');
		$classifiers->add('00000002');

		self::assertCount(2, $classifiers);
		self::assertSame(['00000001', '00000002'], iterator_to_array($classifiers));
		self::assertSame(['00000001', '00000002'], $classifiers->toArray());
	}

	/**
	 * The Contract.lastValidDateUnbounded choice (an empty marker element) is dropped on decode, so it
	 * is excluded from the round-trip; its accessors and the LastValidDateUnbounded entity are pinned here.
	 */
	public function testContractLastValidDateUnboundedAPI(): void
	{
		$contract  = new S\Contract('CT-1', $this->date('2021-01-01'));
		$unbounded = new S\LastValidDateUnbounded;

		$contract->setLastValidDateUnbounded($unbounded);
		self::assertSame($unbounded, $contract->getLastValidDateUnbounded());

		$contract->lastValidDateUnbounded = $unbounded;
		self::assertSame($unbounded, $contract->lastValidDateUnbounded);
	}

	/**
	 * Documents a known 1.x limitation: a boolean set to false is written as "false" but decoded back
	 * as true (PHP casts the non-empty string "false" to true). Only true booleans round-trip. This
	 * test pins the current behaviour so the 2.0 rewrite can detect when it changes.
	 */
	public function testBooleanFalseDecodingLimitation(): void
	{
		$manager = ISDOC\Manager::create();

		$invoice = $this->buildMinimalInvoice();
		$payment = new S\Payment('121.0', S\Payment::PAYMENT_MEANS_CODE_CASH_PAYMENT);
		$payment->setPartialPayment(false);
		$invoice->setPaymentMeans((new S\PaymentMeans)->add($payment));

		$xml = $manager->getWriter()->xml($invoice);
		self::assertStringContainsString('partialPayment="false"', $xml);

		$decoded        = $manager->getReader()->xml($xml);
		$decodedPayment = $this->first($this->notNull($decoded->getPaymentMeans()));

		// The false flag comes back as true — the known limitation.
		self::assertTrue($decodedPayment->getPartialPayment());
	}

	/** A bare, valid invoice with a single line — the minimum the encoder accepts. */
	private function buildMinimalInvoice(): ISDOC\Invoice
	{
		$invoice = new ISDOC\Invoice(
			'2021-0002',
			'00000000-0000-0000-0000-000000005678',
			$this->date('2021-08-16'),
			false,
			'CZK',
			new S\AccountingSupplierParty($this->buildBasicParty('12345678', 'Dodavatel, a. s.')),
		);

		$invoice->getInvoiceLines()->add(new S\InvoiceLine(
			'1',
			'100.0',
			'121.0',
			'21.0',
			'100.0',
			'121.0',
			new S\ClassifiedTaxCategory('21', S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP),
		));

		return $invoice;
	}

	private function date(string $date): DateTimeImmutable
	{
		return DateTimeImmutable::createFromFormat('Y-m-d', $date) ?: throw new LogicException("Bad date $date.");
	}

	/**
	 * @template T of object
	 * @param T|null $value
	 * @return T
	 */
	private function notNull(?object $value): object
	{
		if ($value === null) {
			self::fail('Expected a non-null value.');
		}

		return $value;
	}

	/**
	 * @template T
	 * @param iterable<T> $items
	 * @return T
	 */
	private function first(iterable $items): mixed
	{
		foreach ($items as $item) {
			return $item;
		}

		throw new LogicException('Expected a non-empty collection.');
	}

}
