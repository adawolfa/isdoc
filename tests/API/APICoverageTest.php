<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC\API;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice as S;
use Adawolfa\ISDOC\WriterException;
use BcMath\Number;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;
use Tests\Adawolfa\ISDOC\Snapshot;

/**
 * Coverage guard for the whole public {@see ISDOC\Schema\Invoice} API surface and the decorated
 * writing API advertised in README.md.
 *
 * The test builds a single, maximally-populated invoice that touches every entity and every property
 * the schema exposes, encodes it to ISDOC through the {@see ISDOC\Manager} facade, decodes it back and
 * reads everything back, asserting the whole object graph survived the round-trip.
 *
 * The flow is written with deliberately explicit, statically-analysable property reads and writes
 * ($invoice->id, $invoice->id = ...) so that PHPStan itself verifies the API exists. The encoded XML
 * is pinned by a snapshot; required values go through the constructors and collection items through add().
 *
 * Only the stable, public surface is asserted here; the internal decoder/encoder API is intentionally
 * left out.
 */
final class APICoverageTest extends TestCase
{

	use Snapshot;

	/**
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testRoundTripViaProperties(): void
	{
		$this->assertViaProperties($this->roundTrip($this->buildViaProperties()));
	}

	/**
	 * Encodes the invoice, pins the exact XML, decodes it back and proves the round-trip is lossless
	 * and idempotent (re-encoding the decoded graph yields the very same XML).
	 *
	 * @throws WriterException
	 * @throws ReaderException
	 */
	private function roundTrip(ISDOC\Invoice $invoice): S
	{
		$manager = ISDOC\Manager::create();

		$xml = $manager->writer->xml($invoice);
		$this->assertSnapshot('api-coverage.xml', $xml);

		$decoded = $manager->reader->xml($xml);
		self::assertSame($xml, $manager->writer->xml($decoded), 'Round-trip is not idempotent.');

		$this->assertReferencesResolved($decoded);

		return $decoded;
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

		$invoice->documentType              = S\DocumentType::Invoice;
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
		$invoice->currRate                  = new Number('25.5');
		$invoice->refCurrRate               = new Number('1.0');
		$invoice->version                   = ISDOC\Invoice::Version;

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
		$invoice->orderReferences = new S\OrderReferences()->add($order);

		$deliveryNote = new S\DeliveryNote('DN-1');
		$deliveryNote->issueDate = $this->date('2021-07-10');
		$deliveryNote->uuid      = '00000000-0000-0000-0000-0000000000a2';
		$invoice->deliveryNoteReferences = new S\DeliveryNoteReferences()->add($deliveryNote);

		$originalDocument = new S\OriginalDocument('OD-1');
		$originalDocument->issueDate = $this->date('2021-05-01');
		$originalDocument->uuid      = '00000000-0000-0000-0000-0000000000a3';
		$invoice->originalDocumentReferences = new S\OriginalDocumentReferences()->add($originalDocument);

		$contract = new S\Contract('CT-1', $this->date('2021-01-01'));
		$contract->uuid            = '00000000-0000-0000-0000-0000000000a4';
		$contract->lastValidDate   = $this->date('2022-01-01');
		$contract->isds_id         = 'contract1';
		$contract->file            = 'CONTRACT-FILE';
		$contract->referenceNumber = 'CONTRACT-REF';
		$invoice->contractReferences = new S\ContractReferences()->add($contract);

		$invoice->invoiceLines->add($this->richLineViaProperties($order, $deliveryNote, $originalDocument, $contract));
		$invoice->invoiceLines->add(new S\InvoiceLine(
			'2',
			new Number('250.0'),
			new Number('302.5'),
			new Number('52.5'),
			new Number('250.0'),
			new Number('302.5'),
			new S\ClassifiedTaxCategory(new Number('21'), S\VATCalculationMethod::FromTheBottom),
		));

		$nonTaxedDeposit = new S\NonTaxedDeposit('NTD-1', '555', new Number('100.0'));
		$nonTaxedDeposit->depositAmountCurr = new Number('4.0');
		$invoice->nonTaxedDeposits = new S\NonTaxedDeposits()->add($nonTaxedDeposit);

		$taxedDeposit = new S\TaxedDeposit(
			'TD-1',
			'556',
			new Number('200.0'),
			new Number('242.0'),
			new S\ClassifiedTaxCategory(new Number('21'), S\VATCalculationMethod::FromTheTop),
		);
		$taxedDeposit->taxableDepositAmountCurr      = new Number('8.0');
		$taxedDeposit->taxInclusiveDepositAmountCurr = new Number('9.68');
		$invoice->taxedDeposits = new S\TaxedDeposits()->add($taxedDeposit);

		$taxTotal = $invoice->taxTotal;
		$taxTotal->taxAmount     = new Number('73.5');
		$taxTotal->taxAmountCurr = new Number('2.88');
		$taxSubTotal = new S\TaxSubTotal(
			new Number('350.0'),
			new Number('73.5'),
			new Number('423.5'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			$this->fullTaxCategoryViaProperties(),
		);
		$taxSubTotal->taxableAmountCurr                = new Number('13.72');
		$taxSubTotal->taxAmountCurr                    = new Number('2.88');
		$taxSubTotal->taxInclusiveAmountCurr           = new Number('16.6');
		$taxSubTotal->alreadyClaimedTaxableAmountCurr  = new Number('0.0');
		$taxSubTotal->alreadyClaimedTaxAmountCurr      = new Number('0.0');
		$taxSubTotal->alreadyClaimedTaxInclusiveAmountCurr = new Number('0.0');
		$taxSubTotal->differenceTaxableAmountCurr      = new Number('0.0');
		$taxSubTotal->differenceTaxAmountCurr          = new Number('0.0');
		$taxSubTotal->differenceTaxInclusiveAmountCurr = new Number('0.0');
		$taxTotal->add($taxSubTotal);

		$total = $invoice->legalMonetaryTotal;
		$total->taxExclusiveAmount                  = new Number('350.0');
		$total->taxExclusiveAmountCurr              = new Number('13.72');
		$total->taxInclusiveAmount                  = new Number('423.5');
		$total->taxInclusiveAmountCurr              = new Number('16.6');
		$total->alreadyClaimedTaxExclusiveAmount    = new Number('0.0');
		$total->alreadyClaimedTaxExclusiveAmountCurr = new Number('0.0');
		$total->alreadyClaimedTaxInclusiveAmount    = new Number('0.0');
		$total->alreadyClaimedTaxInclusiveAmountCurr = new Number('0.0');
		$total->differenceTaxExclusiveAmount        = new Number('0.0');
		$total->differenceTaxExclusiveAmountCurr    = new Number('0.0');
		$total->differenceTaxInclusiveAmount        = new Number('0.0');
		$total->differenceTaxInclusiveAmountCurr    = new Number('0.0');
		$total->paidDepositsAmount                  = new Number('0.0');
		$total->paidDepositsAmountCurr              = new Number('0.0');
		$total->payableRoundingAmount               = new Number('0.5');
		$total->payableRoundingAmountCurr           = new Number('0.02');
		$total->payableAmount                       = new Number('424.0');
		$total->payableAmountCurr                   = new Number('16.62');

		$payment = new S\Payment(new Number('424.0'), S\PaymentMeansCode::CreditTransfer);
		$payment->partialPayment = true;
		$payment->details        = $this->detailsViaProperties();
		$paymentMeans = new S\PaymentMeans()->add($payment);
		$paymentMeans->alternateBankAccounts = $this->alternateBankAccountsViaProperties();
		$invoice->paymentMeans = $paymentMeans;

		$supplement = new S\Supplement(
			'invoice.pdf',
			new S\DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
			'2jmj7l5rSw0yVb/vlWAYkK/YBwk=',
		);
		$supplement->preview = true;
		$invoice->supplementsList = new S\SupplementsList()->add($supplement);

		return $invoice;
	}

	private function richLineViaProperties(
		S\Order $order,
		S\DeliveryNote $deliveryNote,
		S\OriginalDocument $originalDocument,
		S\Contract $contract,
	): S\InvoiceLine
	{
		$line = new S\InvoiceLine('1', new Number('100.0'), new Number('121.0'), new Number('21.0'), new Number('100.0'), new Number('121.0'), $this->fullClassifiedTaxCategoryViaProperties());

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
		$line->lineExtensionAmountCurr                       = new Number('3.92');
		$line->lineExtensionAmountBeforeDiscount             = new Number('110.0');
		$line->lineExtensionAmountTaxInclusiveCurr           = new Number('4.75');
		$line->lineExtensionAmountTaxInclusiveBeforeDiscount = new Number('133.1');
		$line->note    = $this->noteViaProperties('Line note.');
		$line->vatNote = $this->noteViaProperties('§ 47 zákona o DPH');
		$line->item    = $this->itemViaProperties();

		return $line;
	}

	private function fullClassifiedTaxCategoryViaProperties(): S\ClassifiedTaxCategory
	{
		$category = new S\ClassifiedTaxCategory(new Number('21'), S\VATCalculationMethod::FromTheTop);
		$category->vatApplicable = true;
		$localReverseCharge = new S\LocalReverseCharge(S\LocalReverseChargeCode::DeliveryOfGold);
		$localReverseCharge->localReverseChargeQuantity = $this->quantityViaProperties('g', '5');
		$category->localReverseCharge = $localReverseCharge;
		return $category;
	}

	private function fullTaxCategoryViaProperties(): S\TaxCategory
	{
		$category = new S\TaxCategory(new Number('21'));
		$category->taxScheme              = 'VAT';
		$category->vatApplicable          = true;
		$category->localReverseChargeFlag = true;
		return $category;
	}

	private function itemViaProperties(): S\Item
	{
		$item = new S\Item();
		$item->description                        = 'Premium widget';
		$item->catalogueItemIdentification        = new S\CatalogueItemIdentification('CAT-1');
		$item->sellersItemIdentification          = new S\SellersItemIdentification('SELLER-1');
		$item->secondarySellersItemIdentification = new S\SecondarySellersItemIdentification('SELLER-2');
		$item->tertiarySellersItemIdentification  = new S\TertiarySellersItemIdentification('SELLER-3');
		$item->buyersItemIdentification           = new S\BuyersItemIdentification('BUYER-1');

		$storeBatch = new S\StoreBatch('BATCH-1', $this->quantityViaProperties('ks', '10'), S\BatchOrSerialNumber::Batch);
		$storeBatch->expirationDate = $this->date('2025-12-31');
		$storeBatch->specification  = 'Cold storage';
		$storeBatch->sealSeriesID   = 'SEAL-1';
		$storeBatch->note           = $this->noteViaProperties('Handle with care.');
		$item->storeBatches = new S\StoreBatches()->add($storeBatch);

		return $item;
	}

	private function detailsViaProperties(): S\Details
	{
		$details = new S\Details();
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
		$account = new S\AlternateBankAccount();
		$account->id       = '987654321';
		$account->bankCode = '0100';
		$account->name     = 'Komerční banka, a. s.';
		$account->iban     = 'CZ6501000000000987654321';
		$account->bic      = 'KOMBCZPP';
		return new S\AlternateBankAccounts()->add($account);
	}

	private function fullPartyViaProperties(string $id, string $name): S\Party
	{
		$party = $this->buildBasicParty($id, $name);

		$party->partyIdentification->catalogFirmIdentification = 'CAT-FIRM-' . $id;
		$party->partyIdentification->userID                    = 'USER-' . $id;

		$schemes = new S\PartyTaxSchemes();
		$schemes->add(new S\PartyTaxScheme('CZ' . $id, 'VAT'));
		$schemes->add(new S\PartyTaxScheme('SK' . $id, 'TIN'));
		$party->partyTaxSchemes = $schemes;

		$register = new S\RegisterIdentification();
		$register->registerKeptAt  = 'Městský soud v Praze';
		$register->registerFileRef = 'B 1234';
		$register->registerDate    = $this->date('2001-03-14');
		$register->preformatted    = 'Spisová značka B 1234 vedená u Městského soudu v Praze';
		$party->registerIdentification = $register;

		$contact = new S\Contact();
		$contact->name           = 'Jan Novák';
		$contact->telephone      = '+420123456789';
		$contact->electronicMail = 'jan.novak@example.com';
		$party->contact = $contact;

		return $party;
	}

	private function noteViaProperties(string $content, ?string $languageID = null): S\Note
	{
		$note = new S\Note();
		$note->content = $content;

		if ($languageID !== null) {
			$note->languageID = $languageID;
		}

		return $note;
	}

	private function quantityViaProperties(string $unitCode, string $content): S\Quantity
	{
		$quantity = new S\Quantity();
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
	// Reading everything back through magic properties.
	// ---------------------------------------------------------------------------------------------

	private function assertViaProperties(S $invoice): void
	{
		self::assertSame(S\DocumentType::Invoice, $invoice->documentType);
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
		self::assertSame('25.5', (string) $invoice->currRate);
		self::assertSame('1.0', (string) $invoice->refCurrRate);
		self::assertSame(ISDOC\Invoice::Version, $invoice->version);

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
		self::assertSame('100.0', (string) $line->lineExtensionAmount);
		self::assertSame('121.0', (string) $line->lineExtensionAmountTaxInclusive);
		self::assertSame('21.0', (string) $line->lineExtensionTaxAmount);
		self::assertSame('100.0', (string) $line->unitPrice);
		self::assertSame('121.0', (string) $line->unitPriceTaxInclusive);
		self::assertSame('00000099', $line->egovClassifier);
		self::assertSame('3.92', (string) $line->lineExtensionAmountCurr);
		self::assertSame('110.0', (string) $line->lineExtensionAmountBeforeDiscount);
		self::assertSame('4.75', (string) $line->lineExtensionAmountTaxInclusiveCurr);
		self::assertSame('133.1', (string) $line->lineExtensionAmountTaxInclusiveBeforeDiscount);

		$category = $line->classifiedTaxCategory;
		self::assertSame('21', (string) $category->percent);
		self::assertSame(S\VATCalculationMethod::FromTheTop, $category->vatCalculationMethod);
		self::assertTrue($category->vatApplicable);
		$localReverseCharge = $this->notNull($category->localReverseCharge);
		self::assertSame(S\LocalReverseChargeCode::DeliveryOfGold, $localReverseCharge->localReverseChargeCode);
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
		self::assertSame(S\BatchOrSerialNumber::Batch, $storeBatch->batchOrSerialNumber);
		self::assertSame('ks', $storeBatch->quantity->unitCode);
		self::assertSame('10', $storeBatch->quantity->content);
		self::assertSame('2025-12-31', $this->notNull($storeBatch->expirationDate)->format('Y-m-d'));
		self::assertSame('Cold storage', $storeBatch->specification);
		self::assertSame('SEAL-1', $storeBatch->sealSeriesID);
		self::assertSame('Handle with care.', $this->notNull($storeBatch->note)->content);

		$nonTaxedDeposit = $this->first($this->notNull($invoice->nonTaxedDeposits));
		self::assertSame('NTD-1', $nonTaxedDeposit->id);
		self::assertSame('555', $nonTaxedDeposit->variableSymbol);
		self::assertSame('100.0', (string) $nonTaxedDeposit->depositAmount);
		self::assertSame('4.0', (string) $nonTaxedDeposit->depositAmountCurr);

		$taxedDeposit = $this->first($this->notNull($invoice->taxedDeposits));
		self::assertSame('TD-1', $taxedDeposit->id);
		self::assertSame('556', $taxedDeposit->variableSymbol);
		self::assertSame('200.0', (string) $taxedDeposit->taxableDepositAmount);
		self::assertSame('242.0', (string) $taxedDeposit->taxInclusiveDepositAmount);
		self::assertSame('8.0', (string) $taxedDeposit->taxableDepositAmountCurr);
		self::assertSame('9.68', (string) $taxedDeposit->taxInclusiveDepositAmountCurr);
		self::assertSame('21', (string) $taxedDeposit->classifiedTaxCategory->percent);

		$taxTotal = $invoice->taxTotal;
		self::assertSame('73.5', (string) $taxTotal->taxAmount);
		self::assertSame('2.88', (string) $taxTotal->taxAmountCurr);
		$taxSubTotal = $this->first($taxTotal);
		self::assertSame('350.0', (string) $taxSubTotal->taxableAmount);
		self::assertSame('73.5', (string) $taxSubTotal->taxAmount);
		self::assertSame('423.5', (string) $taxSubTotal->taxInclusiveAmount);
		self::assertSame('13.72', (string) $taxSubTotal->taxableAmountCurr);
		self::assertSame('16.6', (string) $taxSubTotal->taxInclusiveAmountCurr);
		$taxCategory = $taxSubTotal->taxCategory;
		self::assertSame('21', (string) $taxCategory->percent);
		self::assertSame('VAT', $taxCategory->taxScheme);
		self::assertTrue($taxCategory->vatApplicable);
		self::assertTrue($taxCategory->localReverseChargeFlag);

		$total = $invoice->legalMonetaryTotal;
		self::assertSame('350.0', (string) $total->taxExclusiveAmount);
		self::assertSame('13.72', (string) $total->taxExclusiveAmountCurr);
		self::assertSame('423.5', (string) $total->taxInclusiveAmount);
		self::assertSame('16.6', (string) $total->taxInclusiveAmountCurr);
		self::assertSame('0.0', (string) $total->alreadyClaimedTaxExclusiveAmount);
		self::assertSame('0.0', (string) $total->differenceTaxExclusiveAmount);
		self::assertSame('0.0', (string) $total->paidDepositsAmount);
		self::assertSame('0.5', (string) $total->payableRoundingAmount);
		self::assertSame('0.02', (string) $total->payableRoundingAmountCurr);
		self::assertSame('424.0', (string) $total->payableAmount);
		self::assertSame('16.62', (string) $total->payableAmountCurr);

		$payment = $this->first($this->notNull($invoice->paymentMeans));
		self::assertSame('424.0', (string) $payment->paidAmount);
		self::assertSame(S\PaymentMeansCode::CreditTransfer, $payment->paymentMeansCode);
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
		self::assertSame('http://www.w3.org/2000/09/xmldsig#sha1', $supplement->digestMethod->algorithm);
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

		$schemes = $this->notNull($party->partyTaxSchemes);
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
		$line = $this->first($invoice->invoiceLines);

		self::assertSame($this->first($this->notNull($invoice->orderReferences)), $this->notNull($line->order)->order);
		self::assertSame($this->first($this->notNull($invoice->deliveryNoteReferences)), $this->notNull($line->deliveryNote)->deliveryNote);
		self::assertSame($this->first($this->notNull($invoice->originalDocumentReferences)), $this->notNull($line->originalDocument)->originalDocument);
		self::assertSame($this->first($this->notNull($invoice->contractReferences)), $this->notNull($line->contract)->contract);
	}

	/**
	 * EgovClassifiers is a Collection<string>; the current encoder cannot serialize it, so it is not
	 * part of the round-trip. Its public collection API is pinned here on its own.
	 */
	public function testEgovClassifiersAPI(): void
	{
		$classifiers = new S\EgovClassifiers();
		$classifiers->add('00000001');
		$classifiers->add('00000002');

		self::assertCount(2, $classifiers);
		self::assertSame(['00000001', '00000002'], iterator_to_array($classifiers));
	}

	/**
	 * The Contract.lastValidDateUnbounded choice (an empty marker element) is dropped on decode, so it
	 * is excluded from the round-trip; its property and the LastValidDateUnbounded entity are pinned here.
	 */
	public function testContractLastValidDateUnboundedAPI(): void
	{
		$contract  = new S\Contract('CT-1', $this->date('2021-01-01'));
		$unbounded = new S\LastValidDateUnbounded();

		$contract->lastValidDateUnbounded = $unbounded;
		self::assertSame($unbounded, $contract->lastValidDateUnbounded);
	}

	/**
	 * A boolean set to false round-trips correctly: it is written as "false" and decoded back as false.
	 * The 1.x line had a bug here — it decoded the non-empty string "false" as true — which the 2.0
	 * rewrite fixes by parsing the literal. This test pins the corrected behavior.
	 * @throws ReaderException
	 * @throws WriterException
	 */
	public function testBooleanFalseRoundTrips(): void
	{
		$manager = ISDOC\Manager::create();

		$invoice = $this->buildMinimalInvoice();
		$payment = new S\Payment(new Number('121.0'), S\PaymentMeansCode::CashPayment);
		$payment->partialPayment = false;
		$invoice->paymentMeans = new S\PaymentMeans()->add($payment);

		$xml = $manager->writer->xml($invoice);
		self::assertStringContainsString('partialPayment="false"', $xml);

		$decoded        = $manager->reader->xml($xml);
		$decodedPayment = $this->first($this->notNull($decoded->paymentMeans));

		// The false flag round-trips correctly.
		self::assertFalse($decodedPayment->partialPayment);
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

		$invoice->invoiceLines->add(new S\InvoiceLine(
			'1',
			new Number('100.0'),
			new Number('121.0'),
			new Number('21.0'),
			new Number('100.0'),
			new Number('121.0'),
			new S\ClassifiedTaxCategory(new Number('21'), S\VATCalculationMethod::FromTheTop),
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