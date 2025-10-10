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
	 * @throws SupplementException
	 */
	public function saveTo(string $filename): void
	{
		if (@copy($this->getPath(), $filename) === false) {
			throw ISDOC\SupplementException::couldNotWriteFile($this->getFilename(), $filename);
		}
	}

	public function getContents(): string
	{
		$contents = @file_get_contents($this->getPath());

		if ($contents === false) {
			throw new ISDOC\RuntimeException('Failed to read contents of the supplement.');
		}

		return $contents;
	}

}