<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\X;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use Adawolfa\ISDOC\SupplementException;
use ZipArchive;

/**
 * ISDOCX attachment.
 *
 * @property-read string   $contents
 * @property-read resource $stream
 * @property-read bool     $ok
 */
final class Supplement extends ISDOC\Schema\Invoice\Supplement implements ISDOC\Invoice\RemoteSupplement
{

	/**
	 * Default uncompressed-size cap for a single supplement, enforced before the entry is inflated to defend
	 * against a ZIP decompression bomb (a tiny archive can declare a multi-gigabyte entry). 32 MB is a generous
	 * default for a real attachment while still bounding memory and disk use; callers that legitimately need more
	 * can raise it, or pass null to disable it, on getContents() / getStream() / saveTo().
	 */
	public const SIZE_LIMIT = 1 << 25;

	private ZipArchive $zip;

	public function __construct(
		string       $filename,
		DigestMethod $digestMethod,
		string       $digestValue,
		ZipArchive   $zip
	)
	{
		parent::__construct($filename, $digestMethod, $digestValue);
		$this->zip = $zip;
	}

	/**
	 * @param int|null $sizeLimit Uncompressed-size cap enforced before inflating; null disables it.
	 * @throws SupplementException
	 */
	public function getContents(?int $sizeLimit = self::SIZE_LIMIT): string
	{
		$name = $this->findRealFile();
		$this->checkSize($name, $sizeLimit);
		$contents = $this->zip->getFromName($name);

		if ($contents === false) {
			throw SupplementException::zipDoesNotContainFile($this->getFilename());
		}

		return $contents;
	}

	/**
	 * @param int|null $sizeLimit Uncompressed-size cap enforced before inflating; null disables it.
	 * @return resource
	 * @throws SupplementException
	 */
	public function getStream(?int $sizeLimit = self::SIZE_LIMIT)
	{
		$name = $this->findRealFile();
		$this->checkSize($name, $sizeLimit);
		$resource = $this->zip->getStream($name);

		if ($resource === false) {
			throw SupplementException::zipDoesNotContainFile($this->getFilename());
		}

		return $resource;
	}

	/**
	 * Rejects an oversized entry from its central-directory size before any bytes are inflated. A null limit
	 * disables the check.
	 *
	 * @throws SupplementException
	 */
	private function checkSize(string $name, ?int $sizeLimit): void
	{
		if ($sizeLimit === null) {
			return;
		}

		$size = Zip::entrySize($this->zip, $name);

		if ($size !== null && $size > $sizeLimit) {
			throw SupplementException::supplementTooLarge($this->getFilename(), $size, $sizeLimit);
		}
	}

	/**
	 * @param int|null $sizeLimit Uncompressed-size cap (central-directory pre-check and a running budget on the
	 *                            inflated stream); raise it for legitimately large attachments or pass null to
	 *                            disable it. Defaults to {@see self::SIZE_LIMIT}.
	 * @throws SupplementException
	 */
	public function saveTo(string $filename, ?int $sizeLimit = self::SIZE_LIMIT): void
	{
		// I assume rewound descriptor. getStream() already enforced the size cap from the central directory.
		$resource = $this->getStream($sizeLimit);

		try {

			$f = @fopen($filename, 'w');

			if ($f === false) {
				throw SupplementException::couldNotWriteFile($this->getFilename(), $filename);
			}

			$written  = 0;
			$complete = false;

			try {

				while (!feof($resource)) {

					$chunk = @fread($resource, 1 << 14);

					if ($chunk === false) {
						throw SupplementException::couldNotWriteFile($this->getFilename(), $filename);
					}

					$written += strlen($chunk);

					// Defence in depth: a stream that inflates past the declared size (or a malformed entry) is
					// stopped here so a decompression bomb cannot fill the disk even when statName() under-reports.
					if ($sizeLimit !== null && $written > $sizeLimit) {
						throw SupplementException::supplementTooLarge($this->getFilename(), $written, $sizeLimit);
					}

					if (@fwrite($f, $chunk) === false) {
						throw SupplementException::couldNotWriteFile($this->getFilename(), $filename);
					}

				}

				$complete = true;

			} finally {
				fclose($f);

				// Never leave a partial (or bomb-truncated) file behind when the copy did not finish cleanly.
				if (!$complete) {
					@unlink($filename);
				}
			}

		} finally {
			fclose($resource);
		}
	}

	/** @throws SupplementException */
	public function isOk(): bool
	{
		return ISDOC\Utils::checkSupplementDigest($this);
	}

	/**
	 * File names are apparently case insensitive, but ZIP is not.
	 */
	private function findRealFile(): string
	{
		$lName = str_replace('\\', '/', mb_strtolower($this->getFilename()));

		for ($i = 0; $i < $this->zip->numFiles; $i++) {

			$real = $this->zip->getNameIndex($i);

			if ($real !== false && mb_strtolower($real) === $lName) {
				return $real;
			}

		}

		return $this->getFilename();
	}

}