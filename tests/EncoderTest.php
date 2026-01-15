<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa;
use Adawolfa\ISDOC\WriterException;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony;

final class EncoderTest extends TestCase
{

	use Snapshot;

	/**
	 * @throws WriterException
	 */
	public function testSample(): void
	{
		$invoice = new Adawolfa\ISDOC\Invoice(
			'12345',
			'00000000-0000-0000-0000-000000001234',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException,
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

		$payment->details                 = new Adawolfa\ISDOC\Schema\Invoice\Details;
		$payment->details->id             = '12345678';
		$payment->details->bankCode       = '0800';
		$payment->details->name           = 'Česká spořitelna, a. s.';
		$payment->details->variableSymbol = '123456';
		$payment->details->paymentDueDate = DateTimeImmutable::createFromFormat('Y-m-d', '2022-02-02')
			?: throw new LogicException;

		$invoice->paymentMeans = new Adawolfa\ISDOC\Schema\Invoice\PaymentMeans;
		$invoice->paymentMeans->add($payment);

		$encoded = Adawolfa\ISDOC\Manager::create()->getWriter()->xml($invoice);
		$this->assertSnapshot('encoder-sample.xml', $encoded);
	}

	public function testSimplifiedTaxDocument(): void
	{
		$invoice = new Adawolfa\ISDOC\Invoice(
			'12345',
			'00000000-0000-0000-0000-000000001234',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException,
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

		$invoice->setAccountingCustomerParty(new Adawolfa\ISDOC\Schema\Invoice\AccountingCustomerParty(
			new Adawolfa\ISDOC\Schema\Invoice\Party(
				new Adawolfa\ISDOC\Schema\Invoice\PartyIdentification('87654321'),
				new Adawolfa\ISDOC\Schema\Invoice\PartyName('Customer, a. s.'),
				new Adawolfa\ISDOC\Schema\Invoice\PostalAddress(
					'Dlouhá',
					'1234',
					'Praha',
					'100 01',
					new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika')
				)
			)
		));

		$invoice->invoiceLines->add(new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
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
		));

		$invoice->taxTotal->taxAmount = '21.0';
		$invoice->taxTotal->add(new Adawolfa\ISDOC\Schema\Invoice\TaxSubTotal(
			'100.0',
			'21.0',
			'21.0',
			'0.0',
			'0.0',
			'0.0',
			'0.0',
			'0.0',
			'0.0',
			new Adawolfa\ISDOC\Schema\Invoice\TaxCategory('21'),
		));

		$anonymousCustomerParty = new Adawolfa\ISDOC\Schema\Invoice\AnonymousCustomerParty('123');
		$anonymousCustomerParty->idScheme = 'https://www.rfc-editor.org/rfc/rfc9562.html';
		$invoice->setAnonymousCustomerParty($anonymousCustomerParty);

		$encoded = Adawolfa\ISDOC\Manager::create()->getWriter()->xml($invoice);
		$this->assertSnapshot('encoder-simplified-tax-document.xml', $encoded);
	}

	public function testLegalMonetaryTotalSum(): void
	{
		$invoice = new Adawolfa\ISDOC\Invoice(
			'12345',
			'00000000-0000-0000-0000-000000001234',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException,
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

		$invoice->setAccountingCustomerParty(new Adawolfa\ISDOC\Schema\Invoice\AccountingCustomerParty(
			new Adawolfa\ISDOC\Schema\Invoice\Party(
				new Adawolfa\ISDOC\Schema\Invoice\PartyIdentification('87654321'),
				new Adawolfa\ISDOC\Schema\Invoice\PartyName('Customer, a. s.'),
				new Adawolfa\ISDOC\Schema\Invoice\PostalAddress(
					'Dlouhá',
					'1234',
					'Praha',
					'100 01',
					new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika')
				)
			)
		));

		$invoice->invoiceLines->add(new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'1',
			'50.1',
			'60.621',
			'10.521',
			'50.1',
			'60.621',
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				'21',
				Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP,
			),
		));

		$invoice->invoiceLines->add(new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'2',
			'50.21',
			'60.7541',
			'10.5441',
			'50.21',
			'60.7541',
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				'21',
				Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP,
			),
		));

		$invoice->taxTotal->taxAmount = '21.0';
		$invoice->taxTotal->add(new Adawolfa\ISDOC\Schema\Invoice\TaxSubTotal(
			'100.31',
			'21.0651',
			'121.3751',
			'0.0',
			'0.0',
			'0.0',
			'0.0',
			'0.0',
			'0.0',
			new Adawolfa\ISDOC\Schema\Invoice\TaxCategory('21'),
		));

		$anonymousCustomerParty = new Adawolfa\ISDOC\Schema\Invoice\AnonymousCustomerParty('123');
		$anonymousCustomerParty->idScheme = 'https://www.rfc-editor.org/rfc/rfc9562.html';
		$invoice->setAnonymousCustomerParty($anonymousCustomerParty);

		$encoded = Adawolfa\ISDOC\Manager::create()->getWriter()->xml($invoice);
		$this->assertSnapshot('encoder-legal-monetary-total.xml', $encoded);
	}

}