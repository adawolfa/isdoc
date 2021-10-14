<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use PHPUnit\Framework\TestCase;
use Adawolfa;
use DateTimeImmutable;

final class ReferenceTest extends TestCase
{

	public function testReference(): void
	{
		$invoice = new Adawolfa\ISDOC\Invoice(
			'12345',
			'00000000-0000-0000-0000-000000001234',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16'),
			false,
			'CZK',
			new Adawolfa\ISDOC\Schema\Invoice\AccountingSupplierParty(
				new Adawolfa\ISDOC\Schema\Invoice\Party(
					new Adawolfa\ISDOC\Schema\Invoice\PartyIdentification('12345678'),
					new Adawolfa\ISDOC\Schema\Invoice\PartyName('Firma, a. s.'),
					new Adawolfa\ISDOC\Schema\Invoice\PostalAddress(
						'Dlouhá',
						'1234',
						'Praha',
						'100 01',
						new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika')
					)
				)
			)
		);

		$order = new Adawolfa\ISDOC\Schema\Invoice\Order('123456');

		$invoice->orderReferences = new Adawolfa\ISDOC\Schema\Invoice\OrderReferences;
		$invoice->orderReferences->add($order);

		$line = new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'1',
			'100.0',
			'121.0',
			'21.0',
			'100.0',
			'121.0',
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				'21',
				Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP,
			),
		);

		$line->order = new Adawolfa\ISDOC\Schema\Invoice\OrderLine($order);
		$line->order->lineID = '10';

		$invoice->invoiceLines->add($line);

		$manager  = Adawolfa\ISDOC\Manager::create();
		$read     = $manager->reader->xml($manager->writer->xml($invoice));

		/** @var $readLine Adawolfa\ISDOC\Schema\Invoice\InvoiceLine */
		$readLine = iterator_to_array($read->invoiceLines)[0];

		$this->assertNotNull($readLine->order);
		$this->assertSame(iterator_to_array($read->orderReferences)[0], $readLine->order->order);
		$this->assertSame('10', $readLine->order->lineID);
	}

}