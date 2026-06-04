<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\SupplementException;
use Adawolfa\ISDOC\WriterException;
use BcMath\Number;
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
	 * The size cap on PDF embedded files is a configurable default on the PDF reader: it can be lowered to reject
	 * content the default allows, raised for larger embedded files, or set to {@code null} to disable the check
	 * entirely.
	 *
	 * @throws ReaderException
	 */
	public function testConfigurableSupplementSizeLimit(): void
	{
		$file    = __DIR__ . '/fixtures/read-colsys.pdf';
		$manager = Adawolfa\ISDOC\Manager::create();
		self::assertNotNull($manager->reader->pdfReader);

		// The default cap reads the fixture in full.
		self::assertNotNull($manager->reader->file($file)->supplementsList);

		// A 1-byte cap rejects the embedded file up front, before it is decompressed.
		$manager->reader->pdfReader->supplementSizeLimit = 1;

		try {
			$manager->reader->file($file);
			self::fail('Expected the lowered size limit to reject the embedded file.');
		} catch (ReaderException) {
			// Expected.
		}

		// null disables the cap entirely.
		$manager->reader->pdfReader->supplementSizeLimit = null;
		self::assertNotNull($manager->reader->file($file)->supplementsList);
	}

	/**
	 * A negative cap is nonsensical and is refused at assignment.
	 */
	public function testNegativeSupplementSizeLimitIsRejected(): void
	{
		$manager = Adawolfa\ISDOC\Manager::create();
		self::assertNotNull($manager->reader->pdfReader);

		$this->expectException(Adawolfa\ISDOC\RuntimeException::class);
		$manager->reader->pdfReader->supplementSizeLimit = -1;
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
		$supplements = new Adawolfa\ISDOC\Schema\Invoice\SupplementsList();
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromPath($filename));
		$invoice->supplementsList = $supplements;
		$manager->writer->file($invoice, $this->temp);

		$read = $manager->reader->file($this->temp);
		$this->assertNotNull($read->supplementsList);
		$this->assertGreaterThanOrEqual(1, $read->supplementsList->count());
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
		$supplements = new Adawolfa\ISDOC\Schema\Invoice\SupplementsList();
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromPath(__DIR__ . '/fixtures/append-microsoft.pdf'));
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromString('hello world', 'attachment.txt'));
		$invoice->supplementsList = $supplements;

		$manager->writer->file($invoice, $this->temp);

		$read = $manager->reader->file($this->temp);
		$this->assertNotNull($read->supplementsList);
		$this->assertCount(2, $read->supplementsList);

		$supplementsRead = iterator_to_array($read->supplementsList);
		$txt             = $supplementsRead[0];
		$pdf             = $supplementsRead[1];

		$this->assertInstanceOf(Adawolfa\ISDOC\Invoice\RemoteSupplement::class, $pdf);
		$this->assertInstanceOf(Adawolfa\ISDOC\Invoice\RemoteSupplement::class, $txt);

		$this->assertSame(basename($this->temp), $pdf->filename);
		$this->assertStringStartsWith('%PDF-', $pdf->contents);

		$this->assertSame('attachment.txt', $txt->filename);
		$this->assertSame('hello world', $txt->contents);
	}

	/**
	 * When the carrier trailer ends with /Root as its last entry, the indirect reference
	 * must be parsed without swallowing the closing ">>", otherwise the rebuilt trailer
	 * becomes "… 22 0 R >> /Prev …", a duplicate-">>" dictionary that some PDF tools reject.
	 *
	 * @throws SupplementException
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testAppendTrailerRootLast(): void
	{
		$carrier = file_get_contents(__DIR__ . '/fixtures/append-pdfobject.pdf');
		self::assertNotFalse($carrier);

		// Reorder the trailer dictionary so /Root is its final entry, immediately followed by ">>".
		$carrier = str_replace('/Size 42 /Root 22 0 R /Info', '/Size 42 /Info', $carrier, $removed);
		self::assertSame(1, $removed);
		$carrier = str_replace("] >>\nstartxref", "] /Root 22 0 R >>\nstartxref", $carrier, $appended);
		self::assertSame(1, $appended);

		$carrierFile = sprintf('%s/isdoc_test_carrier.%d.pdf', sys_get_temp_dir(), getmypid());
		self::assertNotFalse(file_put_contents($carrierFile, $carrier));

		try {

			$manager     = Adawolfa\ISDOC\Manager::create();
			$invoice     = self::createInvoice();
			$supplements = new Adawolfa\ISDOC\Schema\Invoice\SupplementsList();
			$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromPath($carrierFile));
			$invoice->supplementsList = $supplements;

			$manager->writer->file($invoice, $this->temp);

			$produced = file_get_contents($this->temp);
			self::assertNotFalse($produced);

			// The appended trailer must keep "/Root 22 0 R" intact and never grow a stray "22 0 R >> /Prev".
			self::assertStringContainsString('/Root 22 0 R /Prev', $produced);
			self::assertStringNotContainsString('22 0 R >> /Prev', $produced);

			$read = $manager->reader->file($this->temp);
			self::assertNotNull($read->supplementsList);
			self::assertGreaterThanOrEqual(1, $read->supplementsList->count());

		} finally {
			@unlink($carrierFile);
		}
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

		$invoice->invoiceLines->add(
			new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
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
			),
		);

		return $invoice;
	}

}