<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
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

}