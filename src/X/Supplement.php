<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\X;
use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use ZipArchive;
use Adawolfa\ISDOC\SupplementException;

/**
 * ISDOCX attachment.
 *
 * @property-read string   $contents
 * @property-read resource $stream
 * @property-read bool     $ok
 */
final class Supplement extends ISDOC\Schema\Invoice\Supplement
{

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
	public function getContents(): string
	{
		$contents = $this->zip->getFromName($this->findRealFile());

		if ($contents === false) {
			throw SupplementException::zipDoesNotContainFile($this->getFilename());
		}

		return $contents;
	}

	/**
	 * @throws SupplementException
	 * @return resource
	 */
	public function getStream()
	{
		$resource = $this->zip->getStream($this->findRealFile());

		if ($resource === false) {
			throw SupplementException::zipDoesNotContainFile($this->getFilename());
		}

		return $resource;
	}

	/** @throws SupplementException */
	public function saveTo(string $filename): void
	{
		// I assume rewound descriptor.
		$resource = $this->getStream();
		$f        = @fopen($filename, 'w');

		if ($f === false) {
			throw SupplementException::couldNotWriteFile($this->getFilename(), $filename);
		}

		while (!feof($resource)) {

			$chunk = @fread($resource, 1 << 14);

			if ($chunk === false || @fwrite($f, $chunk) === false) {
				throw SupplementException::couldNotWriteFile($this->getFilename(), $filename);
			}

		}

		fclose($f);
	}

	/** @throws SupplementException */
	public function isOk(): bool
	{
		switch ($this->getDigestMethod()->algorithm) {

			case 'http://www.w3.org/2000/09/xmldsig#sha1':
				$contents = $this->contents;
				return base64_encode(sha1($contents, true)) === $this->getDigestValue()
					|| sha1($contents) === strtolower($this->getDigestValue());

			default: throw SupplementException::unsupportedDigestAlgo($this->getFilename(), $this->getDigestMethod()->algorithm);

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

			if (mb_strtolower($real) === $lName) {
				return $real;
			}

		}

		return $this->getFilename();
	}

}