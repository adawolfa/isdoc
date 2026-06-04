<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\PDF;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\SupplementException;
use Smalot\PdfParser\PDFObject;

/**
 * PDF ISDOC attachment.
 *
 * @property-read string $contents
 * @property-read bool   $ok
 */
final class Supplement extends ISDOC\Schema\Invoice\Supplement implements ISDOC\Invoice\RemoteSupplement
{

	private PDFObject $object;

	public function __construct(PDFObject $object)
	{
		$this->object = $object;

		parent::__construct(
			$object->getDetails()['F'] ?? 'unknown.dat',
			new ISDOC\Schema\Invoice\DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
			base64_encode(sha1($this->contents, true)),
		);
	}

	/**
	 * @throws SupplementException
	 */
	public function saveTo(string $filename): void
	{
		if (@file_put_contents($filename, $this->getContents()) === false) {
			throw ISDOC\SupplementException::couldNotWriteFile($this->getFilename(), $filename);
		}
	}

	/** {@inheritDoc} */
	public function getContents(): string
	{
		return $this->object->getContent()
			   ?? throw new ISDOC\RuntimeException('Failed to get contents of the PDF embedded file.');
	}

	/** {@inheritDoc} */
	public function isOk(): bool
	{
		return true;
	}

}