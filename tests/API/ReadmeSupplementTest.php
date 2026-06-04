<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC\API;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice as S;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Pins the supplement / ISDOCX API advertised in README.md: building supplements from a path or a
 * string, attaching them through SupplementsList, and the RemoteSupplement read-back surface
 * (ok / filename / contents / saveTo).
 */
final class ReadmeSupplementTest extends TestCase
{

	/** @var string[] */
	private array $temp = [];

	/** The decorated, file-backed Adawolfa\ISDOC\Invoice\Supplement public API. */
	public function testDecoratedSupplementApi(): void
	{
		$path     = $this->tempFile('source');
		$contents = "%PDF-1.4 binary contents";
		file_put_contents($path, $contents);

		$supplement = ISDOC\Invoice\Supplement::fromPath($path, 'document.pdf');

		self::assertSame('document.pdf', $supplement->getFilename());
		self::assertSame($path, $supplement->getPath());
		self::assertSame($contents, $supplement->getContents());
		self::assertSame('http://www.w3.org/2000/09/xmldsig#sha1', $supplement->getDigestMethod()->getAlgorithm());
		self::assertSame(base64_encode(sha1($contents, true)), $supplement->getDigestValue());
		self::assertTrue($supplement->isOk());

		$supplement->setPreview(true);
		self::assertTrue($supplement->getPreview());

		$saveTo = $this->tempFile('saved');
		$supplement->saveTo($saveTo);
		self::assertSame($contents, file_get_contents($saveTo));

		// Without an explicit filename, fromPath derives it from the path's basename.
		$autoNamed = ISDOC\Invoice\Supplement::fromPath($path);
		self::assertSame(basename($path), $autoNamed->getFilename());

		// fromString writes to a temporary file under the hood and keeps the given basename.
		$fromString = ISDOC\Invoice\Supplement::fromString('hello world', 'note.txt');
		self::assertSame('note.txt', $fromString->getFilename());
		self::assertSame('hello world', $fromString->getContents());
		self::assertTrue($fromString->isOk());
	}

	/** Documented failure modes of the decorated, file-backed supplement. */
	public function testDecoratedSupplementErrors(): void
	{
		$missing = sprintf('%s/isdoc_missing_%d.bin', sys_get_temp_dir(), getmypid());
		@unlink($missing);

		// A digest cannot be computed for a file that does not exist.
		try {
			ISDOC\Invoice\Supplement::fromPath($missing);
			self::fail('Expected a SupplementException.');
		} catch (ISDOC\SupplementException) {
			$this->addToAssertionCount(1);
		}

		// Reading the contents after the backing file disappears fails.
		$path = $this->tempFile('vanishing');
		file_put_contents($path, 'data');
		$supplement = ISDOC\Invoice\Supplement::fromPath($path);
		unlink($path);

		try {
			$supplement->getContents();
			self::fail('Expected a RuntimeException.');
		} catch (ISDOC\RuntimeException) {
			$this->addToAssertionCount(1);
		}

		// Saving to a path that cannot be written fails.
		$again = $this->tempFile('again');
		file_put_contents($again, 'data');
		$writable = ISDOC\Invoice\Supplement::fromPath($again);

		try {
			$writable->saveTo(sys_get_temp_dir() . '/isdoc_no_such_dir_' . getmypid() . '/nested.bin');
			self::fail('Expected a SupplementException.');
		} catch (ISDOC\SupplementException) {
			$this->addToAssertionCount(1);
		}
	}

	/** The full ISDOCX round-trip from the README, including RemoteSupplement read-back. */
	public function testIsdocxSupplementRoundTrip(): void
	{
		$manager = ISDOC\Manager::create();
		$invoice = $this->minimalInvoice();

		$supplements = new S\SupplementsList;
		$supplements->add(ISDOC\Invoice\Supplement::fromString('attachment body', 'attachment.txt'));
		$invoice->supplementsList = $supplements;

		$isdocx = $this->tempFile('invoice', 'isdocx');
		$manager->writer->file($invoice, $isdocx);

		$read = $manager->reader->file($isdocx);
		self::assertNotNull($read->supplementsList);
		self::assertCount(1, $read->supplementsList);

		$supplement = iterator_to_array($read->supplementsList)[0];
		self::assertInstanceOf(ISDOC\Invoice\RemoteSupplement::class, $supplement);
		self::assertTrue($supplement->isOk());
		self::assertSame('attachment.txt', $supplement->getFilename());
		self::assertSame('attachment body', $supplement->getContents());

		$saveTo = $this->tempFile('extracted');
		$supplement->saveTo($saveTo);
		self::assertSame('attachment body', file_get_contents($saveTo));
	}

	private function minimalInvoice(): ISDOC\Invoice
	{
		$invoice = new ISDOC\Invoice(
			'2021-0003',
			'00000000-0000-0000-0000-000000009999',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException,
			false,
			'CZK',
			new S\AccountingSupplierParty(new S\Party(
				new S\PartyIdentification('12345678'),
				new S\PartyName('Dodavatel, a. s.'),
				new S\PostalAddress('Dlouhá', '1234', 'Praha', '100 01', new S\Country('CZ', 'Česká republika')),
			)),
		);

		$invoice->invoiceLines->add(new S\InvoiceLine(
			'1',
			'100.0',
			'121.0',
			'21.0',
			'100.0',
			'121.0',
			new S\ClassifiedTaxCategory('21', S\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP),
		));

		return $invoice;
	}

	private function tempFile(string $prefix, string $extension = 'tmp'): string
	{
		$path = sprintf('%s/isdoc_%s_%d_%d.%s', sys_get_temp_dir(), $prefix, getmypid(), count($this->temp), $extension);
		$this->temp[] = $path;
		return $path;
	}

	protected function tearDown(): void
	{
		foreach ($this->temp as $path) {
			@unlink($path);
		}

		$this->temp = [];
	}

}
