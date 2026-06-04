<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\WriterException;
use BcMath\Number;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class ReferenceTest extends TestCase
{

	/**
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testReference(): void
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

		$order = new Adawolfa\ISDOC\Schema\Invoice\Order('123456');

		$orderReferences = new Adawolfa\ISDOC\Schema\Invoice\OrderReferences();
		$orderReferences->add($order);
		$invoice->orderReferences = $orderReferences;

		$line = new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
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

		$orderLine         = new Adawolfa\ISDOC\Schema\Invoice\OrderLine($order);
		$orderLine->lineID = '10';
		$line->order       = $orderLine;

		$invoice->invoiceLines->add($line);

		$manager = Adawolfa\ISDOC\Manager::create();
		$read    = $manager->reader->xml($manager->writer->xml($invoice));

		/** @var Adawolfa\ISDOC\Schema\Invoice\InvoiceLine $readLine */
		$readLine  = iterator_to_array($read->invoiceLines)[0];
		$readOrder = $readLine->order;

		$this->assertNotNull($readOrder);

		$orderReferences = $read->orderReferences;
		$this->assertNotNull($orderReferences);

		$this->assertSame(iterator_to_array($orderReferences)[0], $readOrder->order);
		$this->assertSame('10', $readOrder->lineID);
	}

}