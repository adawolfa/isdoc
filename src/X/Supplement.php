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
		get {
			$contents = $this->zip->getFromName($this->findRealFile());

			if ($contents === false) {
				throw SupplementException::zipDoesNotContainFile($this->filename);
			}

			return $contents;
		}
	}

	/** @var resource */
	public $stream {
		/** @throws SupplementException */
		get {
			$resource = $this->zip->getStream($this->findRealFile());

			if ($resource === false) {
				throw SupplementException::zipDoesNotContainFile($this->filename);
			}

			return $resource;
		}
	}

	/** @throws SupplementException */
	public function saveTo(string $filename): void
	{
		$resource = $this->stream;
		$handle   = @fopen($filename, 'w');

		if ($handle === false) {
			throw SupplementException::couldNotWriteFile($this->filename, $filename);
		}

		while (!feof($resource)) {

			$chunk = @fread($resource, 1 << 14);

			if ($chunk === false || @fwrite($handle, $chunk) === false) {
				throw SupplementException::couldNotWriteFile($this->filename, $filename);
			}

		}

		fclose($handle);
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