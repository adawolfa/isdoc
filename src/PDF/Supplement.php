<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\PDF;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use Smalot\PdfParser\PDFObject;

/**
 * Supplement backed by an embedded file inside a PDF.
 */
final class Supplement extends ISDOC\Schema\Invoice\Supplement implements ISDOC\Invoice\RemoteSupplement
{

	private PDFObject $object;

	public function __construct(PDFObject $object)
	{
		$this->object = $object;

		$name = $object->getDetails()['F'] ?? null;

		parent::__construct(
			is_string($name) ? $name : 'unknown.dat',
			new DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
			base64_encode(sha1($this->contents, true)),
		);
	}

	public string $contents {
		get => $this->object->getContent()
			?? throw new ISDOC\RuntimeException('Failed to get contents of the PDF embedded file.');
	}

	public bool $ok {
		get => true;
	}

	public function saveTo(string $filename): void
	{
		if (@file_put_contents($filename, $this->contents) === false) {
			throw ISDOC\SupplementException::couldNotWriteFile($this->filename, $filename);
		}
	}

}