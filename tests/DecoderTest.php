<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice\PartyTaxScheme;
use Adawolfa\ISDOC\XML\Exception as XmlException;
use PHPUnit\Framework\TestCase;

final class DecoderTest extends TestCase
{

	/**
	 * @throws ReaderException
	 */
	public function testSampleNoReference(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()->reader->file(__DIR__ . '/fixtures/sample-no-reference.isdoc');

		/** @var Adawolfa\ISDOC\Schema\Invoice\InvoiceLine $invoiceLine */
		$invoiceLine = iterator_to_array($invoice->invoiceLines)[0];

		$this->assertIsIterable($invoice->orderReferences);

		/** @var Adawolfa\ISDOC\Schema\Invoice\Order $order */
		$order       = iterator_to_array($invoice->orderReferences)[0];
		$inlineOrder = $invoiceLine->order?->order;
		$this->assertNotNull($inlineOrder);

		// The inline (non-IDREF) reference carries the same order data as the header collection's element...
		$this->assertSame($order->salesOrderID, $inlineOrder->salesOrderID);
		$this->assertSame($order->externalOrderID, $inlineOrder->externalOrderID);
		$this->assertEquals($order->issueDate, $inlineOrder->issueDate);

		// ... but is a distinct object from it.
		$this->assertNotSame($inlineOrder, $order);
	}

	/**
	 * Parsing never fails wholesale: a missing required value raises only when that property is accessed,
	 * naming the exact path.
	 *
	 * @throws ReaderException
	 */
	public function testMissingRequiredRaisesOnlyOnAccess(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()
			->reader
			->file(__DIR__ . '/fixtures/no-vat-applicable.isdoc');

		// Unrelated valid fields read fine even though VATApplicable is missing.
		$this->assertSame('FV-111999/2011', $invoice->id);

		$this->expectException(XmlException::class);
		$this->expectExceptionMessage("Missing required value 'Invoice/VATApplicable'.");
		$invoice->vatApplicable;
	}

	/**
	 * @throws ReaderException
	 */
	public function testNamespacedReferences(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()->reader->file(__DIR__ . '/fixtures/sample-namespaced-references.isdoc');

		$orderReferences = $invoice->orderReferences;
		$this->assertNotNull($orderReferences);
		$orderReference = iterator_to_array($orderReferences)[0];

		$deliveryNoteReferences = $invoice->deliveryNoteReferences;
		$this->assertNotNull($deliveryNoteReferences);
		$deliveryNoteReference = iterator_to_array($deliveryNoteReferences)[0];

		$this->assertNotSame($deliveryNoteReference, $orderReference);

		$firstInvoiceLine = iterator_to_array($invoice->invoiceLines)[0];

		$order = $firstInvoiceLine->order;
		$this->assertNotNull($order);

		$deliveryNote = $firstInvoiceLine->deliveryNote;
		$this->assertNotNull($deliveryNote);

		// The same id="ref" resolves to different targets under different wrapper-name scopes.
		$this->assertSame($order->order, $orderReference);
		$this->assertSame($deliveryNote->deliveryNote, $deliveryNoteReference);
	}

	/**
	 * @throws ReaderException
	 */
	public function testMultiPartyTaxScheme(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()->reader->file(__DIR__ . '/fixtures/multi-partytax.isdoc');
		$party   = $invoice->accountingSupplierParty->party;

		// The collection exposes every scheme in document order.
		$this->assertNotNull($party->partyTaxSchemes);
		$this->assertCount(2, $party->partyTaxSchemes);

		/** @var list<PartyTaxScheme> $schemes */
		$schemes = iterator_to_array($party->partyTaxSchemes);
		$this->assertSame('VAT', $schemes[0]->taxScheme);
		$this->assertSame('CZ25097563', $schemes[0]->companyID);
		$this->assertSame('TIN', $schemes[1]->taxScheme);
		$this->assertSame('SK25097563', $schemes[1]->companyID);

		// A specific scheme is selected by filtering the collection (replacing the old preferredTaxScheme hack).
		$tin = null;

		foreach ($party->partyTaxSchemes as $scheme) {
			if (strtoupper($scheme->taxScheme) === 'TIN') {
				$tin = $scheme;
				break;
			}
		}

		$this->assertNotNull($tin);
		$this->assertSame('SK25097563', $tin->companyID);
	}

}