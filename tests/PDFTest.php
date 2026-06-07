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

		$read = $manager->reader->file($this->temp);
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

	/**
	 * The embedded ISDOC document and supplements must be reachable the spec-defined way — through the catalog's
	 * /Names → /EmbeddedFiles name tree → /Filespec → /EF stream — so conforming readers (Acrobat, pdf.js, poppler,
	 * PdfPig/PDFBox, …) list them as attachments, not only this library's brute-force /EmbeddedFile scan.
	 *
	 * @throws SupplementException
	 * @throws WriterException
	 */
	#[DataProvider('getAppendFiles')]
	public function testEmbeddedFilesAreDiscoverableViaCatalog(string $filename): void
	{
		$manager     = Adawolfa\ISDOC\Manager::create();
		$invoice     = self::createInvoice();
		$supplements = new Adawolfa\ISDOC\Schema\Invoice\SupplementsList;
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromPath($filename));
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromString('hello world', 'attachment.txt'));
		$invoice->supplementsList = $supplements;

		$manager->writer->file($invoice, $this->temp);

		$produced = file_get_contents($this->temp);
		self::assertNotFalse($produced);

		[$names] = self::walkEmbeddedFilesNameTree($produced);

		// Both the ISDOC document and the loose supplement are listed in the catalog name tree, …
		self::assertArrayHasKey('invoice.isdoc', $names);
		self::assertArrayHasKey('attachment.txt', $names);

		// … and every name-tree entry resolves through a /Filespec to a real /EmbeddedFile stream.
		foreach ($names as $name => $resolvesToStream) {
			self::assertTrue($resolvesToStream, "name tree entry '$name' does not resolve to an embedded stream");
		}
	}

	/**
	 * When the carrier's catalog already has a /Names dictionary (the Colsys carrier references one holding /Dests),
	 * the new /EmbeddedFiles entry must be merged into it rather than replacing it, so the carrier's existing named
	 * destinations survive.
	 *
	 * @throws SupplementException
	 * @throws WriterException
	 */
	public function testCatalogNamesMergePreservesExistingEntries(): void
	{
		$manager     = Adawolfa\ISDOC\Manager::create();
		$invoice     = self::createInvoice();
		$supplements = new Adawolfa\ISDOC\Schema\Invoice\SupplementsList;
		$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromPath(__DIR__ . '/fixtures/append-colsys.pdf'));
		$invoice->supplementsList = $supplements;

		$manager->writer->file($invoice, $this->temp);

		$produced = file_get_contents($this->temp);
		self::assertNotFalse($produced);

		[$names, $namesDict] = self::walkEmbeddedFilesNameTree($produced);

		self::assertArrayHasKey('invoice.isdoc', $names);
		self::assertNotNull($namesDict);
		self::assertStringContainsString('/EmbeddedFiles', $namesDict);
		self::assertStringContainsString('/Dests', $namesDict); // the carrier's pre-existing entry is kept
	}

	/**
	 * Walks the produced PDF the way a conforming reader does: catalog → /Names → /EmbeddedFiles → /Filespec → /EF,
	 * using the latest revision of each indirect object (incremental-update semantics).
	 *
	 * @return array{0: array<string, bool>, 1: ?string} discovered name ⇒ stream-resolves, and the names dictionary
	 */
	private static function walkEmbeddedFilesNameTree(string $pdf): array
	{
		$objects = self::pdfObjects($pdf);
		$root    = self::pdfLastRoot($pdf);
		$names   = [];

		if ($root === null || !isset($objects[$root])) {
			return [$names, null];
		}

		$namesDict = self::resolveDictionary($objects, self::pdfValue($objects[$root], '/Names'));

		if ($namesDict === null) {
			return [$names, null];
		}

		$tree = self::resolveDictionary($objects, self::pdfValue($namesDict, '/EmbeddedFiles'));

		if ($tree === null) {
			return [$names, $namesDict];
		}

		$array = self::pdfValue($tree, '/Names');

		if ($array === null || $array[0] !== 'array') {
			return [$names, $namesDict];
		}

		if (preg_match_all('~\(([^)]*)\)\s+(\d+)\s+0\s+R~', $array[1], $pairs, PREG_SET_ORDER)) {
			foreach ($pairs as $pair) {

				$filespec = $objects[(int) $pair[2]] ?? '';
				$ef       = self::pdfValue($filespec, '/EF');
				$resolves = false;

				if ($ef !== null && $ef[0] === 'dict' && preg_match('~/F\s+(\d+)\s+0\s+R~', $ef[1], $stream)) {
					$num      = (int) $stream[1];
					$resolves = isset($objects[$num]) && str_contains($objects[$num], '/EmbeddedFile');
				}

				$names[$pair[1]] = $resolves;

			}
		}

		return [$names, $namesDict];
	}

	/**
	 * @return array<int, string> object number ⇒ body, latest definition winning
	 */
	private static function pdfObjects(string $pdf): array
	{
		$objects = [];

		if (preg_match_all('~(?<![0-9])(\d+)\s+(\d+)\s+obj\b~', $pdf, $matches, PREG_OFFSET_CAPTURE)) {
			foreach ($matches[0] as $i => $hit) {
				$start = $hit[1] + strlen($hit[0]);
				$end   = strpos($pdf, 'endobj', $start);
				if ($end !== false) {
					$objects[(int) $matches[1][$i][0]] = substr($pdf, $start, $end - $start);
				}
			}
		}

		return $objects;
	}

	private static function pdfLastRoot(string $pdf): ?int
	{
		if (preg_match_all('~/Root\s+(\d+)\s+\d+\s+R~', $pdf, $matches)) {
			return (int) end($matches[1]);
		}

		return null;
	}

	/**
	 * Extracts a dictionary value: an indirect reference, an inline dictionary, or an array.
	 *
	 * @return array{0: 'ref', 1: int}|array{0: 'dict'|'array', 1: string}|null
	 */
	private static function pdfValue(string $dictionary, string $key): ?array
	{
		$at = strpos($dictionary, $key);

		if ($at === false) {
			return null;
		}

		$rest = ltrim(substr($dictionary, $at + strlen($key)));

		if (preg_match('~^(\d+)\s+\d+\s+R~', $rest, $match)) {
			return ['ref', (int) $match[1]];
		}

		if (str_starts_with($rest, '<<')) {
			$depth  = 0;
			$at     = 0;
			$length = strlen($rest);

			while ($at < $length - 1) {
				$two = substr($rest, $at, 2);
				if ($two === '<<') {
					$depth++;
					$at += 2;
				} elseif ($two === '>>') {
					$depth--;
					$at += 2;
					if ($depth === 0) {
						return ['dict', substr($rest, 0, $at)];
					}
				} else {
					$at++;
				}
			}

			return null;
		}

		if (str_starts_with($rest, '[')) {
			$end = strpos($rest, ']');
			if ($end !== false) {
				return ['array', substr($rest, 0, $end + 1)];
			}
		}

		return null;
	}

	/**
	 * @param array<int, string>                                              $objects
	 * @param array{0: 'ref', 1: int}|array{0: 'dict'|'array', 1: string}|null $value
	 */
	private static function resolveDictionary(array $objects, ?array $value): ?string
	{
		if ($value === null) {
			return null;
		}

		if ($value[0] === 'ref') {
			return $objects[$value[1]] ?? null;
		}

		return $value[0] === 'dict' ? $value[1] : null;
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