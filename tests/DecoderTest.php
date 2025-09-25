<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa;
use Adawolfa\ISDOC\ReaderException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionObject;
use Symfony;

final class DecoderTest extends TestCase
{

	use Snapshot;

	/**
	 * @throws ReaderException
	 * @throws JsonException
	 */
	public function testSample(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()->getReader()->file(__DIR__ . '/fixtures/sample.isdoc');
		$data    = $invoice->toArray();

		self::walkArrayDateToString($data);

		$this->assertInstanceOf(Adawolfa\ISDOC\Schema\Invoice::class, $invoice);
		$this->assertSnapshot('decoder-sample.json', Json::encode($data, Json::PRETTY));
	}

	/**
	 * @throws ReaderException
	 */
	public function testSampleNoReference(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()->getReader()->file(__DIR__ . '/fixtures/sample-no-reference.isdoc');

		/** @var Adawolfa\ISDOC\Schema\Invoice\InvoiceLine $invoiceLine */
		$invoiceLine = iterator_to_array($invoice->invoiceLines)[0];

		$this->assertIsIterable($invoice->orderReferences);

		/** @var Adawolfa\ISDOC\Schema\Invoice\Order $order */
		$order = iterator_to_array($invoice->orderReferences)[0];

		$invoiceLineArray = $invoiceLine->order?->order->toArray();
		$orderArray       = $order->toArray();

		$invoiceLineArray['issueDate'] = $invoiceLine->order?->order->issueDate?->format('Y-m-d');
		$orderArray['issueDate']       = $order->issueDate?->format('Y-m-d');

		$this->assertSame($invoiceLineArray, $orderArray);
		$this->assertNotSame($invoiceLine->order?->order, $order);
	}

	/**
	 * @throws ReflectionException
	 * @throws ReaderException
	 */
	public function testSkipMissingPrimitiveValuesHydration(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create(true)
			->getReader()
			->file(__DIR__ . '/fixtures/no-vat-applicable.isdoc');

		$reflection = new ReflectionObject($invoice);
		$property = $reflection->getProperty('vatApplicable');
		$property->setAccessible(true);
		$this->assertFalse($property->isInitialized($invoice));
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

}