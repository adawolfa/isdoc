<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice as S;
use Adawolfa\ISDOC\SupplementException;
use Adawolfa\ISDOC\WriterException;
use BcMath\Number;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;
use ZipArchive;

/**
 * Untrusted-input hardening (see SECURITY-REVIEW.md):
 *
 * - SEC-01: a ZIP decompression bomb is rejected from the central-directory size before any byte is inflated.
 * - SEC-03: saveTo() cannot fill the disk — the size guard fires before the destination is even opened.
 */
final class SecurityTest extends TestCase
{

	/** @var string[] */
	private array $temp = [];

	/**
	 * SEC-01 — a tiny archive declaring an over-cap ISDOC entry is rejected before getFromName() inflates it.
	 *
	 * The fixture is a few hundred bytes on disk but declares 300 KB uncompressed (> the 256 KB document cap),
	 * exactly the shape of a decompression bomb. The throw proves the cap is enforced from the central directory.
	 */
	public function testOversizedIsdocEntryRejectedBeforeInflation(): void
	{
		$path = $this->tempFile('bomb', 'isdocx');

		$zip = new ZipArchive();
		self::assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
		$zip->addFromString('invoice.isdoc', str_repeat('A', 300 * 1024));
		$zip->close();

		self::assertLessThan(64 * 1024, (int) filesize($path), 'Fixture must stay tiny to prove no inflation.');

		$manager = ISDOC\Manager::create();

		try {
			$manager->reader->file($path);
			self::fail('Expected a ReaderException for the oversized entry.');
		} catch (ReaderException $exception) {
			self::assertStringContainsString('exceeding', $exception->getMessage());
		}
	}

	/**
	 * SEC-01 / SEC-03 — a supplement that declares an over-cap uncompressed size throws on every access path
	 * (contents / ok / saveTo) instead of materialising hundreds of megabytes, and saveTo() writes nothing.
	 *
	 * A valid ISDOCX is produced through the writer, then the supplement entry's declared uncompressed size is
	 * forged up to 300 MB (> the 32 MB supplement cap) while the archive stays tiny — a faithful bomb.
	 *
	 * @throws SupplementException
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testOversizedSupplementRejectedBeforeInflation(): void
	{
		$payloadSize = 300 * 1024;
		$invoice     = $this->minimalInvoice();

		$supplements = new S\SupplementsList();
		$supplements->add(ISDOC\Invoice\Supplement::fromString(str_repeat('B', $payloadSize), 'bomb.bin'));
		$invoice->supplementsList = $supplements;

		$path    = $this->tempFile('supplement-bomb', 'isdocx');
		$manager = ISDOC\Manager::create();
		$manager->writer->file($invoice, $path);

		$this->forgeEntrySize($path, $payloadSize, 300 * 1024 * 1024);

		$read       = $manager->reader->file($path);
		$supplement = iterator_to_array($read->supplementsList ?? [])[0] ?? null;
		self::assertInstanceOf(ISDOC\X\Supplement::class, $supplement);

		$this->assertThrowsSupplement(fn() => $supplement->contents, 'contents');
		$this->assertThrowsSupplement(fn() => $supplement->ok, 'ok');

		$dest = $this->tempFile('extracted');
		$this->assertThrowsSupplement(fn() => $supplement->saveTo($dest), 'saveTo');
		self::assertFileDoesNotExist($dest, 'saveTo() must not write a partial file when the cap is exceeded.');
	}

	/**
	 * The 32 MB cap is only a default: getContents()/getStream()/saveTo() take the caller's own limit, rejecting
	 * content the default would have allowed (from the central-directory size, before inflation) and reading it once
	 * widened — and {@code null} disables the cap altogether.
	 *
	 * @throws SupplementException
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testCustomSupplementSizeLimitIsHonoured(): void
	{
		$payload = 'a tiny attachment'; // 17 bytes
		$invoice = $this->minimalInvoice();

		$supplements = new S\SupplementsList();
		$supplements->add(ISDOC\Invoice\Supplement::fromString($payload, 'note.txt'));
		$invoice->supplementsList = $supplements;

		$path    = $this->tempFile('custom-limit', 'isdocx');
		$manager = ISDOC\Manager::create();
		$manager->writer->file($invoice, $path);

		$read       = $manager->reader->file($path);
		$supplement = iterator_to_array($read->supplementsList ?? [])[0] ?? null;
		self::assertInstanceOf(ISDOC\X\Supplement::class, $supplement);

		// The default cap (and the convenience property) read the payload in full.
		self::assertSame($payload, $supplement->contents);
		self::assertSame($payload, $supplement->getContents());

		// A 4-byte cap rejects the 17-byte entry up front, on every accessor.
		$this->assertThrowsSupplement(fn() => $supplement->getContents(4), 'getContents(4)');
		$this->assertThrowsSupplement(fn() => $supplement->getStream(4), 'getStream(4)');

		$capped = $this->tempFile('capped');
		$this->assertThrowsSupplement(fn() => $supplement->saveTo($capped, 4), 'saveTo(4)');
		self::assertFileDoesNotExist($capped, 'saveTo() must not write a partial file when the cap is exceeded.');

		// A generous explicit cap behaves like the default.
		$stream = $supplement->getStream(1 << 20);
		self::assertSame($payload, stream_get_contents($stream));
		fclose($stream);

		// null disables the cap entirely — the full payload reads back on every accessor.
		self::assertSame($payload, $supplement->getContents(null));

		$uncapped = $supplement->getStream(null);
		self::assertSame($payload, stream_get_contents($uncapped));
		fclose($uncapped);

		$saved = $this->tempFile('uncapped');
		$supplement->saveTo($saved, null);
		self::assertSame($payload, file_get_contents($saved));
	}

	/**
	 * Overwrites the four-byte little-endian uncompressed-size field of an archive entry (present in both the
	 * local file header and the central directory) so {@see ZipArchive::statName()} reports the forged value.
	 */
	private function forgeEntrySize(string $path, int $from, int $to): void
	{
		$bytes = file_get_contents($path);
		self::assertNotFalse($bytes);

		$needle = pack('V', $from);
		self::assertGreaterThanOrEqual(2, substr_count($bytes, $needle), 'Could not locate the size field to forge.');

		self::assertNotFalse(file_put_contents($path, str_replace($needle, pack('V', $to), $bytes)));
	}

	/** @param callable():mixed $access */
	private function assertThrowsSupplement(callable $access, string $label): void
	{
		try {
			$access();
			self::fail("Expected a SupplementException from $label.");
		} catch (SupplementException $exception) {
			self::assertStringContainsString('exceeding', $exception->getMessage(), $label);
		}
	}

	private function minimalInvoice(): ISDOC\Invoice
	{
		$invoice = new ISDOC\Invoice(
			'2021-0003',
			'00000000-0000-0000-0000-000000009999',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException(),
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
			new Number('100.0'),
			new Number('121.0'),
			new Number('21.0'),
			new Number('100.0'),
			new Number('121.0'),
			new S\ClassifiedTaxCategory(new Number('21'), S\VATCalculationMethod::FromTheTop),
		));

		return $invoice;
	}

	private function tempFile(string $prefix, string $extension = 'tmp'): string
	{
		$path = sprintf('%s/isdoc_sec_%s_%d_%d.%s', sys_get_temp_dir(), $prefix, getmypid(), count($this->temp), $extension);
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