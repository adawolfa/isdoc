<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\X;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\SupplementException;
use ZipArchive;

/**
 * Supplement backed by an entry in the ISDOCX ZIP archive.
 *
 * Bound onto the parsed {@code <Supplement>} element by {@see Reader}; the file name and digest are read from
 * the document, the bytes from the archive (whose entry names are matched case-insensitively).
 */
final class Supplement extends ISDOC\Schema\Invoice\Supplement implements ISDOC\Invoice\RemoteSupplement
{

	public ZipArchive $zip {
		set {
			$this->zip = $value;
		}
	}

	public string $contents {
		/** @throws SupplementException */
		get => $this->getContents();
	}

	/** @var resource */
	public $stream {
		/** @throws SupplementException */
		get => $this->getStream();
	}

	/** @throws SupplementException */
	public function getContents(?int $sizeLimit = self::SizeLimit): string
	{
		$name = $this->findRealFile();
		$this->checkSize($name, $sizeLimit);
		$contents = $this->zip->getFromName($name);

		if ($contents === false) {
			throw SupplementException::zipDoesNotContainFile($this->filename);
		}

		return $contents;
	}

	/**
	 * @return resource
	 * @throws SupplementException
	 */
	public function getStream(?int $sizeLimit = self::SizeLimit)
	{
		$name = $this->findRealFile();
		$this->checkSize($name, $sizeLimit);
		$resource = $this->zip->getStream($name);

		if ($resource === false) {
			throw SupplementException::zipDoesNotContainFile($this->filename);
		}

		return $resource;
	}

	/** @throws SupplementException */
	public function saveTo(string $filename, ?int $sizeLimit = self::SizeLimit): void
	{
		$resource = $this->getStream($sizeLimit);

		try {

			$handle = @fopen($filename, 'w');

			if ($handle === false) {
				throw SupplementException::couldNotWriteFile($this->filename, $filename);
			}

			$written  = 0;
			$complete = false;

			try {

				while (!feof($resource)) {

					$chunk = @fread($resource, 1 << 14);

					if ($chunk === false) {
						throw SupplementException::couldNotWriteFile($this->filename, $filename);
					}

					$written += strlen($chunk);

					// Defence in depth: a stream that inflates past the declared size (or a malformed entry) is
					// stopped here so a decompression bomb cannot fill the disk even when statName() under-reports.
					if ($sizeLimit !== null && $written > $sizeLimit) {
						throw SupplementException::supplementTooLarge($this->filename, $written, $sizeLimit);
					}

					if (@fwrite($handle, $chunk) === false) {
						throw SupplementException::couldNotWriteFile($this->filename, $filename);
					}

				}

				$complete = true;

			} finally {
				fclose($handle);

				// Never leave a partial (or bomb-truncated) file behind when the copy did not finish cleanly.
				if (!$complete) {
					@unlink($filename);
				}
			}

		} finally {
			fclose($resource);
		}
	}

	/**
	 * Rejects an oversized entry from its central-directory size before any bytes are inflated.
	 *
	 * @throws SupplementException
	 */
	private function checkSize(string $name, ?int $sizeLimit): void
	{
		$size = Zip::entrySize($this->zip, $name);

		if ($sizeLimit !== null && $size !== null && $size > $sizeLimit) {
			throw SupplementException::supplementTooLarge($this->filename, $size, $sizeLimit);
		}
	}

	public bool $ok {
		/** @throws SupplementException */
		get => ISDOC\Utils::checkSupplementDigest($this);
	}

	/** File names are apparently case insensitive, but the ZIP is not. */
	private function findRealFile(): string
	{
		$name = str_replace('\\', '/', mb_strtolower($this->filename));

		for ($i = 0; $i < $this->zip->numFiles; $i++) {

			$real = $this->zip->getNameIndex($i);

			if ($real !== false && mb_strtolower($real) === $name) {
				return $real;
			}

		}

		return $this->filename;
	}

}