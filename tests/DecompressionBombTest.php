<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use Adawolfa\ISDOC\SupplementException;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class DecompressionBombTest extends TestCase
{

	/** @var string[] */
	private array $temp = [];

	public function testOversizedIsdocEntryRejectedBeforeInflation(): void
	{
		$path = $this->tempFile('document', 'isdocx');
		$zip  = new ZipArchive;

		self::assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
		self::assertTrue($zip->addFromString('invoice.isdoc', str_repeat('A', 300 * 1024)));
		self::assertTrue($zip->close());
		self::assertLessThan(64 * 1024, (int) filesize($path));

		$this->expectException(ReaderException::class);
		$this->expectExceptionMessage('exceeding the 262144 byte limit');

		ISDOC\Manager::create()->reader->file($path);
	}

	public function testOversizedSupplementRejectedOnEveryAccessPath(): void
	{
		[$zip, $supplement] = $this->supplement(str_repeat('B', 2048));
		$destination        = $this->tempFile('rejected');
		unlink($destination);

		foreach ([
			fn() => $supplement->getContents(1024),
			fn() => $supplement->getStream(1024),
			fn() => $supplement->saveTo($destination, 1024),
		] as $access) {
			$this->assertSupplementTooLarge($access, 1024);
		}

		self::assertFileDoesNotExist($destination);
		$zip->close();
	}

	public function testSaveToRemovesPartialFileWhenDeclaredSizeIsUnderreported(): void
	{
		[$zip, $supplement, $path] = $this->supplement(str_repeat('C', 64 * 1024));
		$zip->close();
		$this->forgeEntrySize($path, 1024);

		self::assertTrue($zip->open($path, ZipArchive::RDONLY) === true);
		self::assertSame(1024, $zip->statName('attachment.bin')['size']);

		$destination = $this->tempFile('partial');
		unlink($destination);

		$this->assertSupplementTooLarge(
			fn() => $supplement->saveTo($destination, 20 * 1024),
			20 * 1024,
		);

		self::assertFileDoesNotExist($destination);
		$zip->close();
	}

	/** @param callable(): mixed $access */
	private function assertSupplementTooLarge(callable $access, int $limit): void
	{
		try {
			$access();
			self::fail('Expected an oversized supplement to be rejected.');
		} catch (SupplementException $exception) {
			self::assertStringContainsString("exceeding the $limit byte limit", $exception->getMessage());
		}
	}

	/** @return array{ZipArchive, ISDOC\X\Supplement, string} */
	private function supplement(string $contents): array
	{
		$path = $this->tempFile('supplement', 'isdocx');
		$zip  = new ZipArchive;

		self::assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
		self::assertTrue($zip->addFromString('attachment.bin', $contents));
		self::assertTrue($zip->close());
		self::assertTrue($zip->open($path, ZipArchive::RDONLY) === true);

		return [
			$zip,
			new ISDOC\X\Supplement(
				'attachment.bin',
				new DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
				'',
				$zip,
			),
			$path,
		];
	}

	private function forgeEntrySize(string $path, int $size): void
	{
		$contents = file_get_contents($path);
		self::assertIsString($contents);

		$localHeader      = strpos($contents, "PK\x03\x04");
		$centralDirectory = strpos($contents, "PK\x01\x02");
		self::assertIsInt($localHeader);
		self::assertIsInt($centralDirectory);

		$contents = substr_replace($contents, pack('V', $size), $localHeader + 22, 4);
		$contents = substr_replace($contents, pack('V', $size), $centralDirectory + 24, 4);

		self::assertSame(strlen($contents), file_put_contents($path, $contents));
	}

	private function tempFile(string $prefix, string $extension = 'tmp'): string
	{
		$path = tempnam(sys_get_temp_dir(), "isdoc_$prefix");
		self::assertIsString($path);

		if ($extension !== 'tmp') {
			$renamed = "$path.$extension";
			self::assertTrue(rename($path, $renamed));
			$path = $renamed;
		}

		$this->temp[] = $path;

		return $path;
	}

	protected function tearDown(): void
	{
		foreach ($this->temp as $path) {
			@unlink($path);
		}
	}

}
