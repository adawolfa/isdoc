<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Symfony;
use Adawolfa;
use Doctrine;

final class DecoderTest extends TestCase
{

	use Snapshot;

	public function testSample(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()->getReader()->file(__DIR__ . '/fixtures/sample.isdoc');
		$data    = $invoice->toArray();

		self::walkArrayDateToString($data);

		$this->assertInstanceOf(Adawolfa\ISDOC\Schema\Invoice::class, $invoice);
		$this->assertSnapshot('decoder-sample.json', Json::encode($data, Json::PRETTY));
	}

	public function testSampleNoReference(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()->getReader()->file(__DIR__ . '/fixtures/sample-no-reference.isdoc');

		/** @var $invoiceLine Adawolfa\ISDOC\Schema\Invoice\InvoiceLine */
		$invoiceLine = iterator_to_array($invoice->invoiceLines)[0];

		/** @var $order Adawolfa\ISDOC\Schema\Invoice\Order */
		$order = iterator_to_array($invoice->orderReferences)[0];

		$invoiceLineArray = $invoiceLine->order->order->toArray();
		$orderArray       = $order->toArray();

		$invoiceLineArray['issueDate'] = $invoiceLine->order->order->issueDate->format('Y-m-d');
		$orderArray['issueDate']       = $order->issueDate->format('Y-m-d');

		$this->assertSame($invoiceLineArray, $orderArray);
		$this->assertNotSame($invoiceLine->order->order, $order);
	}

    public function testSkipMissingPrimitiveValuesHydration(): void
    {
        $invoice = Adawolfa\ISDOC\Manager::create(true)
            ->getReader()
            ->file(__DIR__ . '/fixtures/no-vat-applicable.isdoc');

        $reflection = new ReflectionObject($invoice);
        $this->assertFalse($reflection->getProperty('vatApplicable')->isInitialized($invoice));
    }

	public function testNamespacedReferences(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()->getReader()->file(__DIR__ . '/fixtures/sample-namespaced-references.isdoc');

		$orderReference = null;
		foreach ($invoice->orderReferences ?? [] as $orderReference);
		$this->assertNotNull($orderReference);

		$deliveryNoteReference = null;
		foreach ($invoice->deliveryNoteReferences ?? [] as $deliveryNoteReference);
		$this->assertNotNull($deliveryNoteReference);

		$this->assertNotSame($deliveryNoteReference, $orderReference);

		$firstInvoiceLine = null;

		foreach ($invoice->invoiceLines as $firstInvoiceLine) {
			break;
		}

		$this->assertNotNull($firstInvoiceLine);

		$this->assertNotNull($firstInvoiceLine->order);
		$this->assertNotNull($firstInvoiceLine->deliveryNote);
		$this->assertSame($firstInvoiceLine->order->order, $orderReference);
		$this->assertSame($firstInvoiceLine->deliveryNote->deliveryNote, $deliveryNoteReference);
	}

	public function testMultiPartyTaxScheme(): void
	{
		$default = Adawolfa\ISDOC\Manager::create()->getReader()->file(__DIR__ . '/fixtures/multi-partytax.isdoc');
		$this->assertNotNull($default->getAccountingSupplierParty()->getParty()->getPartyTaxScheme());
		$this->assertSame($default->getAccountingSupplierParty()->getParty()->getPartyTaxScheme()->getCompanyID(), 'CZ25097563');

		$vat = Adawolfa\ISDOC\Manager::create(false, 'vat')->getReader()->file(__DIR__ . '/fixtures/multi-partytax.isdoc');
		$this->assertNotNull($vat->getAccountingSupplierParty()->getParty()->getPartyTaxScheme());
		$this->assertSame($vat->getAccountingSupplierParty()->getParty()->getPartyTaxScheme()->getCompanyID(), 'CZ25097563');

		$tin = Adawolfa\ISDOC\Manager::create(false, 'tin')->getReader()->file(__DIR__ . '/fixtures/multi-partytax.isdoc');
		$this->assertNotNull($tin->getAccountingSupplierParty()->getParty()->getPartyTaxScheme());
		$this->assertSame($tin->getAccountingSupplierParty()->getParty()->getPartyTaxScheme()->getCompanyID(), 'SK25097563');
	}

}