<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use PHPUnit\Framework\TestCase;
use Adawolfa;
use DateTimeImmutable;

final class XTest extends TestCase
{

	private string $temp;

	public function testWriteRead(): void
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

		$invoice->invoiceLines->add(
			new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
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
			)
		);

		$supplements = new Adawolfa\ISDOC\Schema\Invoice\SupplementsList;
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromString('foo', 'foo.txt'));
		$invoice->supplementsList = $supplements;

		$manager = Adawolfa\ISDOC\Manager::create();
		$manager->writer->file($invoice, $this->temp);

		$read = $manager->reader->file($this->temp);

		$this->assertSame($invoice->id, $read->id);
		$this->assertCount(1, $read->supplementsList);

		/** @var $readSupplement Adawolfa\ISDOC\X\Supplement */
		$readSupplement = iterator_to_array($read->supplementsList)[0];

		$this->assertTrue($readSupplement->ok);
		$this->assertSame('foo.txt', $readSupplement->filename);
		$this->assertSame('foo', $readSupplement->contents);
	}

	protected function setUp(): void
	{
		$this->temp = sprintf('%s/isdocx_test.%d.isdocx', sys_get_temp_dir(), getmypid());
	}

	protected function tearDown(): void
	{
		@unlink($this->temp);
	}

}