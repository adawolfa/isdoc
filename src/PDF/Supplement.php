<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\PDF;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use Adawolfa\ISDOC\SupplementException;
use Smalot\PdfParser\PDFObject;

/**
 * Supplement backed by an embedded file inside a PDF.
 */
final class Supplement extends ISDOC\Schema\Invoice\Supplement implements ISDOC\Invoice\RemoteSupplement
{

	private PDFObject $object;

	/**
	 * @param string|null $name Filename from the carrier's /Filespec, where it conformantly belongs. When null, the
	 *                          legacy /F on the embedded-file stream is used as a fallback.
	 * @throws SupplementException
	 */
	public function __construct(PDFObject $object, ?string $name = null)
	{
		$this->object = $object;

		$name ??= $object->getDetails()['F'] ?? null;

		parent::__construct(
			is_string($name) ? $name : 'unknown.dat',
			new DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
			base64_encode(sha1($this->contents, true)),
		);
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
		$contents = $this->object->getContent()
			?? throw new ISDOC\RuntimeException('Failed to get contents of the PDF embedded file.');

		// The reader already rejects an over-cap embedded file from its declared Length; this guards against a
		// stream whose decoded contents exceed it (or a caller passing a tighter limit) before it reaches memory.
		if ($sizeLimit !== null && strlen($contents) > $sizeLimit) {
			throw SupplementException::supplementTooLarge($this->filename, strlen($contents), $sizeLimit);
		}

		return $contents;
	}

	/**
	 * @return resource
	 * @throws SupplementException
	 */
	public function getStream(?int $sizeLimit = self::SizeLimit)
	{
		$contents = $this->getContents($sizeLimit);
		$resource = fopen('php://temp', 'r+b');

		if ($resource === false) {
			throw new ISDOC\RuntimeException('Failed to open a stream over the PDF embedded file.');
		}

		fwrite($resource, $contents);
		rewind($resource);

		return $resource;
	}

	public bool $ok {
		get => true;
	}

	/** @throws SupplementException */
	public function saveTo(string $filename, ?int $sizeLimit = self::SizeLimit): void
	{
		if (@file_put_contents($filename, $this->getContents($sizeLimit)) === false) {
			throw SupplementException::couldNotWriteFile($this->filename, $filename);
		}
	}

}