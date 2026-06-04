<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice as S;
use Adawolfa\ISDOC\SupplementException;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;
use ZipArchive;

/**
 * Untrusted-input hardening (backport of the 2.x SEC-01 / SEC-03 fixes):
 *
 * - SEC-01: a ZIP decompression bomb is rejected from the central-directory size before any byte is inflated.
 * - SEC-03: saveTo() cannot fill the disk — the size guard fires before the destination is even opened, and a
 *   partial file is never left behind.
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

		$zip = new ZipArchive;
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
	 * (contents / isOk / saveTo) instead of materialising hundreds of megabytes, and saveTo() writes nothing.
	 *
	 * A valid ISDOCX is produced through the writer, then the supplement entry's declared uncompressed size is
	 * forged up to 300 MB (> the 256 MB supplement cap) while the archive stays tiny — a faithful bomb.
	 *
	 * @throws SupplementException
	 * @throws ReaderException
	 */
	public function testOversizedSupplementRejectedBeforeInflation(): void
	{
		$payloadSize = 300 * 1024;
		$invoice     = $this->minimalInvoice();

		$supplements = new S\SupplementsList;
		$supplements->add(ISDOC\Invoice\Supplement::fromString(str_repeat('B', $payloadSize), 'bomb.bin'));
		$invoice->supplementsList = $supplements;

		$path    = $this->tempFile('supplement-bomb', 'isdocx');
		$manager = ISDOC\Manager::create();
		$manager->writer->file($invoice, $path);

		$this->forgeEntrySize($path, $payloadSize, 300 * 1024 * 1024);

		$read = $manager->reader->file($path);
		self::assertNotNull($read->supplementsList);

		$supplement = iterator_to_array($read->supplementsList)[0] ?? null;
		self::assertInstanceOf(ISDOC\X\Supplement::class, $supplement);

		$this->assertThrowsSupplement(fn() => $supplement->getContents(), 'getContents');
		$this->assertThrowsSupplement(fn() => $supplement->isOk(), 'isOk');

		$dest = $this->tempFile('extracted');
		$this->assertThrowsSupplement(fn() => $supplement->saveTo($dest), 'saveTo');
		self::assertFileDoesNotExist($dest, 'saveTo() must not write a partial file when the cap is exceeded.');
	}

	/**
	 * The cap is the caller's policy on every read path — getContents(), getStream() and saveTo(): each can be
	 * lowered (rejecting a legitimately small entry from its central-directory size) or disabled with null (reading
	 * the real content through). Uses a real, consistent archive — no forging — so the inflated stream genuinely
	 * matches its declared size.
	 *
	 * @throws SupplementException
	 * @throws ReaderException
	 */
	public function testSupplementSizeLimitIsCallerConfigurable(): void
	{
		$payloadSize = 300 * 1024;
		$invoice     = $this->minimalInvoice();

		$supplements = new S\SupplementsList;
		$supplements->add(ISDOC\Invoice\Supplement::fromString(str_repeat('C', $payloadSize), 'attachment.bin'));
		$invoice->supplementsList = $supplements;

		$path    = $this->tempFile('configurable', 'isdocx');
		$manager = ISDOC\Manager::create();
		$manager->writer->file($invoice, $path);

		$read = $manager->reader->file($path);
		self::assertNotNull($read->supplementsList);

		$supplement = iterator_to_array($read->supplementsList)[0] ?? null;
		self::assertInstanceOf(ISDOC\X\Supplement::class, $supplement);

		// A lowered cap rejects the entry up front on every read path, before inflating.
		$this->assertThrowsSupplement(fn() => $supplement->getContents(1024), 'getContents(1024)');
		$this->assertThrowsSupplement(fn() => $supplement->getStream(1024), 'getStream(1024)');

		$rejected = $this->tempFile('configurable-rejected');
		$this->assertThrowsSupplement(fn() => $supplement->saveTo($rejected, 1024), 'saveTo(1024)');
		self::assertFileDoesNotExist($rejected);

		// A disabled cap (null) reads the real content through.
		self::assertSame($payloadSize, strlen($supplement->getContents(null)));

		$stream = $supplement->getStream(null);
		self::assertSame($payloadSize, strlen((string) stream_get_contents($stream)));
		fclose($stream);

		$allowed = $this->tempFile('configurable-allowed');
		$supplement->saveTo($allowed, null);
		self::assertFileExists($allowed);
		self::assertSame($payloadSize, (int) filesize($allowed));
	}

	/**
	 * The same caller-configurable cap is part of the RemoteSupplement contract, so a local (write-path) supplement
	 * honours it too: a lowered limit rejects it from its on-disk size, null reads it through.
	 *
	 * @throws SupplementException
	 */
	public function testLocalSupplementSizeLimitIsCallerConfigurable(): void
	{
		$supplement = ISDOC\Invoice\Supplement::fromString('payload', 'note.txt');

		self::assertSame('payload', $supplement->getContents());
		self::assertSame('payload', $supplement->getContents(null));
		$this->assertThrowsSupplement(fn() => $supplement->getContents(1), 'local getContents(1)');

		$rejected = $this->tempFile('local-rejected');
		$this->assertThrowsSupplement(fn() => $supplement->saveTo($rejected, 1), 'local saveTo(1)');
		self::assertFileDoesNotExist($rejected);

		$allowed = $this->tempFile('local-allowed');
		$supplement->saveTo($allowed, null);
		self::assertSame('payload', file_get_contents($allowed));
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
		$path = sprintf('%s/isdoc_sec_%s_%d_%d.%s', sys_get_temp_dir(), $prefix, getmypid(), count($this->temp), $extension);
		$this->temp[] = $path;

		return $path;
	}

	protected function tearDown(): void
	{
		foreach (array_reverse($this->temp) as $path) {
			@unlink($path);
		}

		$this->temp = [];
	}

}