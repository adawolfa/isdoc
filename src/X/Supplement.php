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
final class Supplement extends ISDOC\Schema\Invoice\Supplement
{

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

	/** @throws SupplementException */
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

	/** @throws SupplementException */
	public function saveTo(string $filename, ?int $sizeLimit = self::SIZE_LIMIT): void
	{
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
		switch ($this->getDigestMethod()->algorithm) {

			case 'http://www.w3.org/2000/09/xmldsig#sha1':
				$contents = $this->contents;
				return base64_encode(sha1($contents, true)) === $this->getDigestValue()
					   || sha1($contents) === strtolower($this->getDigestValue());

			default:
				throw SupplementException::unsupportedDigestAlgo($this->getFilename(), $this->getDigestMethod()->algorithm);

		}
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