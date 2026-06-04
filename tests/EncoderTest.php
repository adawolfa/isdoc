<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa;
use Adawolfa\ISDOC\WriterException;
use BcMath\Number;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

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
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException(),
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
						new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika'),
					),
				),
			),
		);

		$invoiceLine1 = new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'1',
			new Number('100.0'),
			new Number('121.0'),
			new Number('21.0'),
			new Number('100.0'),
			new Number('121.0'),
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				new Number('21'),
				Adawolfa\ISDOC\Schema\Invoice\VATCalculationMethod::FromTheTop,
			),
		);

		$invoiceLine2 = new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'2',
			new Number('250.0'),
			new Number('250.0'),
			new Number('0.0'),
			new Number('250'),
			new Number('250.0'),
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				new Number('0'),
				Adawolfa\ISDOC\Schema\Invoice\VATCalculationMethod::FromTheTop,
			),
		);

		$quantity = new Adawolfa\ISDOC\Schema\Invoice\Quantity();
		$quantity->unitCode = 'ks';
		$quantity->content = '99';
		$invoiceLine2->invoicedQuantity = $quantity;

		$invoice->invoiceLines->add($invoiceLine1);
		$invoice->invoiceLines->add($invoiceLine2);

		$payment = new Adawolfa\ISDOC\Schema\Invoice\Payment(
			new Number('0.0'),
			Adawolfa\ISDOC\Schema\Invoice\PaymentMeansCode::CashPayment,
		);

		$details                 = new Adawolfa\ISDOC\Schema\Invoice\Details();
		$details->id             = '12345678';
		$details->bankCode       = '0800';
		$details->name           = 'Česká spořitelna, a. s.';
		$details->variableSymbol = '123456';
		$details->paymentDueDate = DateTimeImmutable::createFromFormat('Y-m-d', '2022-02-02')
			?: throw new LogicException();
		$payment->details        = $details;

		$paymentMeans = new Adawolfa\ISDOC\Schema\Invoice\PaymentMeans();
		$paymentMeans->add($payment);
		$invoice->paymentMeans = $paymentMeans;

		$encoded = Adawolfa\ISDOC\Manager::create()->writer->xml($invoice);
		$this->assertSnapshot('encoder-sample.xml', $encoded);
	}

	/**
	 * @throws WriterException
	 */
	public function testSimplifiedTaxDocument(): void
	{
		$invoice = new Adawolfa\ISDOC\Invoice(
			'12345',
			'00000000-0000-0000-0000-000000001234',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException(),
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
						new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika'),
					),
				),
			),
		);

		$invoice->accountingCustomerParty = new Adawolfa\ISDOC\Schema\Invoice\AccountingCustomerParty(
			new Adawolfa\ISDOC\Schema\Invoice\Party(
				new Adawolfa\ISDOC\Schema\Invoice\PartyIdentification('87654321'),
				new Adawolfa\ISDOC\Schema\Invoice\PartyName('Customer, a. s.'),
				new Adawolfa\ISDOC\Schema\Invoice\PostalAddress(
					'Dlouhá',
					'1234',
					'Praha',
					'100 01',
					new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika'),
				),
			),
		);

		$invoice->invoiceLines->add(new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'1',
			new Number('100.0'),
			new Number('121.0'),
			new Number('21.0'),
			new Number('100.0'),
			new Number('121.0'),
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				new Number('21'),
				Adawolfa\ISDOC\Schema\Invoice\VATCalculationMethod::FromTheTop,
			),
		));

		$invoice->taxTotal->taxAmount = new Number('21.0');
		$invoice->taxTotal->add(new Adawolfa\ISDOC\Schema\Invoice\TaxSubTotal(
			new Number('100.0'),
			new Number('21.0'),
			new Number('21.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Adawolfa\ISDOC\Schema\Invoice\TaxCategory(new Number('21')),
		));

		$anonymousCustomerParty = new Adawolfa\ISDOC\Schema\Invoice\AnonymousCustomerParty('123');
		$anonymousCustomerParty->idScheme = 'https://www.rfc-editor.org/rfc/rfc9562.html';
		$invoice->anonymousCustomerParty = $anonymousCustomerParty;

		$encoded = Adawolfa\ISDOC\Manager::create()->writer->xml($invoice);
		$this->assertSnapshot('encoder-simplified-tax-document.xml', $encoded);
	}

	/**
	 * @throws WriterException
	 */
	public function testLegalMonetaryTotalSum(): void
	{
		$invoice = new Adawolfa\ISDOC\Invoice(
			'12345',
			'00000000-0000-0000-0000-000000001234',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException(),
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
						new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika'),
					),
				),
			),
		);

		$invoice->accountingCustomerParty = new Adawolfa\ISDOC\Schema\Invoice\AccountingCustomerParty(
			new Adawolfa\ISDOC\Schema\Invoice\Party(
				new Adawolfa\ISDOC\Schema\Invoice\PartyIdentification('87654321'),
				new Adawolfa\ISDOC\Schema\Invoice\PartyName('Customer, a. s.'),
				new Adawolfa\ISDOC\Schema\Invoice\PostalAddress(
					'Dlouhá',
					'1234',
					'Praha',
					'100 01',
					new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika'),
				),
			),
		);

		$invoice->invoiceLines->add(new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'1',
			new Number('50.1'),
			new Number('60.621'),
			new Number('10.521'),
			new Number('50.1'),
			new Number('60.621'),
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				new Number('21'),
				Adawolfa\ISDOC\Schema\Invoice\VATCalculationMethod::FromTheTop,
			),
		));

		$invoice->invoiceLines->add(new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'2',
			new Number('50.21'),
			new Number('60.7541'),
			new Number('10.5441'),
			new Number('50.21'),
			new Number('60.7541'),
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				new Number('21'),
				Adawolfa\ISDOC\Schema\Invoice\VATCalculationMethod::FromTheTop,
			),
		));

		$invoice->taxTotal->taxAmount = new Number('21.0');
		$invoice->taxTotal->add(new Adawolfa\ISDOC\Schema\Invoice\TaxSubTotal(
			new Number('100.31'),
			new Number('21.0651'),
			new Number('121.3751'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Adawolfa\ISDOC\Schema\Invoice\TaxCategory(new Number('21')),
		));

		$anonymousCustomerParty = new Adawolfa\ISDOC\Schema\Invoice\AnonymousCustomerParty('123');
		$anonymousCustomerParty->idScheme = 'https://www.rfc-editor.org/rfc/rfc9562.html';
		$invoice->anonymousCustomerParty = $anonymousCustomerParty;

		$encoded = Adawolfa\ISDOC\Manager::create()->writer->xml($invoice);
		$this->assertSnapshot('encoder-legal-monetary-total.xml', $encoded);
	}

	/**
	 * @throws WriterException
	 */
	public function testPartyTaxSchemes(): void
	{
		$invoice = new Adawolfa\ISDOC\Invoice(
			'12345',
			'00000000-0000-0000-0000-000000001234',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException(),
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
						new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika'),
					),
				),
			),
		);

		$partyTaxSchemes = new Adawolfa\ISDOC\Schema\Invoice\PartyTaxSchemes();

		$partyTaxSchemes->add(new Adawolfa\ISDOC\Schema\Invoice\PartyTaxScheme(
			'CZ12345678',
			'VAT',
		));

		$partyTaxSchemes->add(new Adawolfa\ISDOC\Schema\Invoice\PartyTaxScheme(
			'SK12345678',
			'TIN',
		));

		$invoice->accountingSupplierParty->party->partyTaxSchemes = $partyTaxSchemes;

		$invoice->accountingCustomerParty = new Adawolfa\ISDOC\Schema\Invoice\AccountingCustomerParty(
			new Adawolfa\ISDOC\Schema\Invoice\Party(
				new Adawolfa\ISDOC\Schema\Invoice\PartyIdentification('87654321'),
				new Adawolfa\ISDOC\Schema\Invoice\PartyName('Customer, a. s.'),
				new Adawolfa\ISDOC\Schema\Invoice\PostalAddress(
					'Dlouhá',
					'1234',
					'Praha',
					'100 01',
					new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika'),
				),
			),
		);

		$invoice->invoiceLines->add(new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
			'1',
			new Number('100.0'),
			new Number('121.0'),
			new Number('21.0'),
			new Number('100.0'),
			new Number('121.0'),
			new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
				new Number('21'),
				Adawolfa\ISDOC\Schema\Invoice\VATCalculationMethod::FromTheTop,
			),
		));

		$invoice->taxTotal->taxAmount = new Number('21.0');
		$invoice->taxTotal->add(new Adawolfa\ISDOC\Schema\Invoice\TaxSubTotal(
			new Number('100.0'),
			new Number('21.0'),
			new Number('21.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Number('0.0'),
			new Adawolfa\ISDOC\Schema\Invoice\TaxCategory(new Number('21')),
		));

		$anonymousCustomerParty = new Adawolfa\ISDOC\Schema\Invoice\AnonymousCustomerParty('123');
		$anonymousCustomerParty->idScheme = 'https://www.rfc-editor.org/rfc/rfc9562.html';
		$invoice->anonymousCustomerParty = $anonymousCustomerParty;

		$encoded = Adawolfa\ISDOC\Manager::create()->writer->xml($invoice);
		$this->assertSnapshot('encoder-party-tax-schemes.xml', $encoded);
	}

}