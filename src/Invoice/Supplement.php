<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use Adawolfa\ISDOC\SupplementException;

/**
 * Supplement with an actual file.
 *
 * @property-read string $path
 * @property-read string $contents
 * @property-read bool   $ok
 */
class Supplement extends ISDOC\Schema\Invoice\Supplement implements RemoteSupplement
{

	/**
	 * Default size cap for the file, enforced from its on-disk size before it is read into memory. 32 MB; callers
	 * can raise it, or pass null to disable it, on getContents() / saveTo().
	 */
	public const SIZE_LIMIT = 1 << 25;

	private string $path;

	public function __construct(
		string       $filename,
		DigestMethod $digestMethod,
		string       $digestValue,
		string       $path
	)
	{
		parent::__construct($filename, $digestMethod, $digestValue);
		$this->path = $path;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	/** @throws ISDOC\SupplementException */
	public static function fromString(string $contents, string $filename): self
	{
		$tempFile = tempnam(sys_get_temp_dir(), 'isdocx_supplement');

		if (@file_put_contents($tempFile, $contents) === false) {
			throw ISDOC\SupplementException::couldNotCreateSupplement($filename);
		}

		return self::fromPath($tempFile, basename($filename));
	}

	/** @throws ISDOC\SupplementException */
	public static function fromPath(string $path, ?string $filename = null): self
	{
		if ($filename === null) {
			$filename = basename($path);
		}

		$sha1 = @sha1_file($path, true);

		if ($sha1 === false) {
			throw ISDOC\SupplementException::couldNotComputeSha1($filename, $path);
		}

		$digestValue = base64_encode($sha1);

		return new self(
			$filename,
			new DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
			$digestValue,
			$path,
		);
	}

	/**
	 * @throws SupplementException
	 */
	public function isOk(): bool
	{
		return ISDOC\Utils::checkSupplementDigest($this);
	}

	/**
	 * @param int|null $sizeLimit Size cap enforced on the file; raise it or pass null to disable it.
	 * @throws SupplementException
	 */
	public function saveTo(string $filename, ?int $sizeLimit = self::SIZE_LIMIT): void
	{
		$this->checkSize($sizeLimit);

		if (@copy($this->getPath(), $filename) === false) {
			throw ISDOC\SupplementException::couldNotWriteFile($this->getFilename(), $filename);
		}
	}

	/**
	 * @param int|null $sizeLimit Size cap enforced on the file; raise it or pass null to disable it.
	 * @throws SupplementException
	 */
	public function getContents(?int $sizeLimit = self::SIZE_LIMIT): string
	{
		$this->checkSize($sizeLimit);

		$contents = @file_get_contents($this->getPath());

		if ($contents === false) {
			throw new ISDOC\RuntimeException('Failed to read contents of the supplement.');
		}

		return $contents;
	}

	/**
	 * Rejects an oversized file from its on-disk size before reading it into memory. A null limit disables the
	 * check.
	 *
	 * @throws SupplementException
	 */
	private function checkSize(?int $sizeLimit): void
	{
		if ($sizeLimit === null) {
			return;
		}

		$size = @filesize($this->getPath());

		if ($size !== false && $size > $sizeLimit) {
			throw ISDOC\SupplementException::supplementTooLarge($this->getFilename(), $size, $sizeLimit);
		}
	}

}