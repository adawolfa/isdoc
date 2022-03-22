<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use PHPUnit\Framework\TestCase;
use Symfony;
use Adawolfa;
use Doctrine;
use DateTimeImmutable;

final class EncoderTest extends TestCase
{

	use Snapshot;

	public function testSample(): void
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

		$invoiceLine1 = new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
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

		$invoiceLine2 = new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'2',
			'250.0',
			'250.0',
			'0.0',
			'250',
			'250.0',
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				'0',
				Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP,
			),
		);

		$quantity = new Adawolfa\ISDOC\Schema\Invoice\Quantity;
		$quantity->setUnitCode('ks');
		$quantity->setContent('99');
		$invoiceLine2->setInvoicedQuantity($quantity);

		$invoice->invoiceLines->add($invoiceLine1);
		$invoice->invoiceLines->add($invoiceLine2);

		$payment = new Adawolfa\ISDOC\Schema\Invoice\Payment(
			'0.0',
			10,
		);

		$payment->details = new Adawolfa\ISDOC\Schema\Invoice\Details;
		$payment->details->id = '12345678';
		$payment->details->bankCode = '0800';
		$payment->details->name = 'Česká spořitelna, a. s.';
		$payment->details->variableSymbol = '123456';
		$payment->details->paymentDueDate = DateTimeImmutable::createFromFormat('Y-m-d', '2022-02-02');

		$invoice->paymentMeans = new Adawolfa\ISDOC\Schema\Invoice\PaymentMeans;
		$invoice->paymentMeans->add($payment);

		$encoded = Adawolfa\ISDOC\Manager::create()->getWriter()->xml($invoice);
		$this->assertSnapshot('encoder-sample.xml', $encoded);
	}

}