<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\SupplementException;
use Adawolfa\ISDOC\WriterException;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PDFTest extends TestCase
{

	private string $temp;

	/**
	 * @throws ReaderException
	 */
	#[DataProvider('getReadFiles')]
	public function testRead(string $filename): void
	{
		$manager = Adawolfa\ISDOC\Manager::create();
		$invoice = $manager->reader->file($filename);
		$this->assertNotNull($invoice);
		$this->assertNotNull($invoice->supplementsList);
		$this->assertGreaterThanOrEqual(1, $invoice->supplementsList->count());
	}

	/**
	 * @return iterable<array{string}>
	 */
	public static function getReadFiles(): iterable
	{
		$files = glob(__DIR__ . '/fixtures/read-*.pdf');
		self::assertNotFalse($files);

		foreach ($files as $filename) {
			yield [$filename];
		}
	}

	/**
	 * @throws WriterException
	 * @throws ReaderException
	 * @throws SupplementException
	 */
	#[DataProvider('getAppendFiles')]
	public function testAppend(string $filename): void
	{
		$manager     = Adawolfa\ISDOC\Manager::create();
		$invoice     = self::createInvoice();
		$supplements = new Adawolfa\ISDOC\Schema\Invoice\SupplementsList;
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromPath($filename));
		$invoice->supplementsList = $supplements;
		$manager->writer->file($invoice, $this->temp);

		$read = $manager->reader->file($this->temp);
		$this->assertNotNull($read);
		$this->assertGreaterThanOrEqual(1, $invoice->supplementsList?->count() ?? 0);
	}

	/**
	 * @return iterable<array{string}>
	 */
	public static function getAppendFiles(): iterable
	{
		$files = glob(__DIR__ . '/fixtures/append-*.pdf');
		self::assertNotFalse($files);

		foreach ($files as $filename) {
			yield [$filename];
		}
	}

	/**
	 * @throws SupplementException
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testSupplement(): void
	{
		$manager     = Adawolfa\ISDOC\Manager::create();
		$invoice     = self::createInvoice();
		$supplements = new Adawolfa\ISDOC\Schema\Invoice\SupplementsList;
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromPath(__DIR__ . '/fixtures/append-microsoft.pdf'));
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromString('hello world', 'attachment.txt'));
		$invoice->supplementsList = $supplements;

		$manager->writer->file($invoice, $this->temp);

		copy($this->temp, __DIR__ . '/../test.pdf');

		$read = $manager->reader->file($this->temp);
		$this->assertNotNull($read);
		$this->assertNotNull($read->supplementsList);
		$this->assertCount(2, $read->supplementsList);

		$supplementsRead = iterator_to_array($read->supplementsList);
		$txt             = $supplementsRead[0];
		$pdf             = $supplementsRead[1];

		$this->assertInstanceOf(Adawolfa\ISDOC\Invoice\RemoteSupplement::class, $pdf);
		$this->assertInstanceOf(Adawolfa\ISDOC\Invoice\RemoteSupplement::class, $txt);

		$this->assertSame(basename($this->temp), $pdf->getFilename());
		$this->assertStringStartsWith('%PDF-', $pdf->getContents());

		$this->assertSame('attachment.txt', $txt->getFilename());
		$this->assertSame('hello world', $txt->getContents());
	}

	protected function setUp(): void
	{
		$this->temp = sprintf('%s/isdoc_test.%d.pdf', sys_get_temp_dir(), getmypid());
	}

	protected function tearDown(): void
	{
		@unlink($this->temp);
	}

	private static function createInvoice(): Adawolfa\ISDOC\Invoice
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

		return $invoice;
	}

}