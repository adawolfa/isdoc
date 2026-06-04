<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use Adawolfa\ISDOC\SupplementException;

/**
 * Supplement backed by a file on disk; the kind a caller attaches when building a document.
 */
class Supplement extends ISDOC\Schema\Invoice\Supplement implements RemoteSupplement
{

	public string $path {
		get {
			return $this->path;
		}
	}

	public function __construct(
		string       $filename,
		DigestMethod $digestMethod,
		string       $digestValue,
		string       $path,
	)
	{
		parent::__construct($filename, $digestMethod, $digestValue);
		$this->path = $path;
	}

	/** @throws SupplementException */
	public static function fromString(string $contents, string $filename): self
	{
		$tempFile = tempnam(sys_get_temp_dir(), 'isdocx_supplement');

		if ($tempFile === false || @file_put_contents($tempFile, $contents) === false) {
			throw SupplementException::couldNotCreateSupplement($filename);
		}

		return self::fromPath($tempFile, basename($filename));
	}

	/** @throws SupplementException */
	public static function fromPath(string $path, ?string $filename = null): self
	{
		$filename ??= basename($path);

		$sha1 = @sha1_file($path, true);

		if ($sha1 === false) {
			throw SupplementException::couldNotComputeSha1($filename, $path);
		}

		return new self(
			$filename,
			new DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
			base64_encode($sha1),
			$path,
		);
	}

	public bool $ok {
		/** @throws SupplementException */
		get => ISDOC\Utils::checkSupplementDigest($this);
	}

	public string $contents {
		/**
		 * @throws SupplementException
		 * @throws ISDOC\RuntimeException
		 */
		get => $this->getContents();
	}

	/** @var resource */
	public $stream {
		/**
		 * @throws SupplementException
		 * @throws ISDOC\RuntimeException
		 */
		get => $this->getStream();
	}

	/**
	 * @throws SupplementException
	 * @throws ISDOC\RuntimeException
	 */
	public function getContents(?int $sizeLimit = self::SizeLimit): string
	{
		$size = @filesize($this->path);

		if ($sizeLimit !== null && $size !== false && $size > $sizeLimit) {
			throw SupplementException::supplementTooLarge($this->filename, $size, $sizeLimit);
		}

		$contents = @file_get_contents($this->path);

		if ($contents === false) {
			throw new ISDOC\RuntimeException('Failed to read contents of the supplement.');
		}

		// Defence in depth if filesize() under-reported (a local file is trusted, but the stat could race a write).
		if ($sizeLimit !== null && strlen($contents) > $sizeLimit) {
			throw SupplementException::supplementTooLarge($this->filename, strlen($contents), $sizeLimit);
		}

		return $contents;
	}

	/**
	 * @return resource
	 * @throws SupplementException
	 * @throws ISDOC\RuntimeException
	 */
	public function getStream(?int $sizeLimit = self::SizeLimit)
	{
		$size = @filesize($this->path);

		if ($sizeLimit !== null && $size !== false && $size > $sizeLimit) {
			throw SupplementException::supplementTooLarge($this->filename, $size, $sizeLimit);
		}

		$resource = @fopen($this->path, 'rb');

		if ($resource === false) {
			throw new ISDOC\RuntimeException('Failed to read contents of the supplement.');
		}

		return $resource;
	}

	/** @throws SupplementException */
	public function saveTo(string $filename, ?int $sizeLimit = self::SizeLimit): void
	{
		$size = @filesize($this->path);

		if ($sizeLimit !== null && $size !== false && $size > $sizeLimit) {
			throw SupplementException::supplementTooLarge($this->filename, $size, $sizeLimit);
		}

		if (@copy($this->path, $filename) === false) {
			throw SupplementException::couldNotWriteFile($this->filename, $filename);
		}
	}

}