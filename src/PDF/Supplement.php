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

	/**
	 * Default size cap for the embedded file, enforced before its contents are handed back to bound memory and disk
	 * use. 32 MB; callers can raise it, or pass null to disable it, on getContents() / saveTo().
	 */
	public const SIZE_LIMIT = 1 << 25;

	private PDFObject $object;

	public function __construct(PDFObject $object)
	{
		$this->object = $object;

		// The digest is computed over the full embedded file independently of any read-time size cap.
		$contents = $object->getContent()
					?? throw new ISDOC\RuntimeException('Failed to get contents of the PDF embedded file.');

		parent::__construct(
			$object->getDetails()['F'] ?? 'unknown.dat',
			new ISDOC\Schema\Invoice\DigestMethod('http://www.w3.org/2000/09/xmldsig#sha1'),
			base64_encode(sha1($contents, true)),
		);
	}

	/**
	 * @param int|null $sizeLimit Size cap enforced on the embedded file; raise it or pass null to disable it.
	 * @throws SupplementException
	 */
	public function saveTo(string $filename, ?int $sizeLimit = self::SIZE_LIMIT): void
	{
		if (@file_put_contents($filename, $this->getContents($sizeLimit)) === false) {
			throw ISDOC\SupplementException::couldNotWriteFile($this->getFilename(), $filename);
		}
	}

	/**
	 * @param int|null $sizeLimit Size cap enforced on the embedded file; raise it or pass null to disable it.
	 * @throws SupplementException
	 * @throws ISDOC\RuntimeException
	 * @deprecated use {@see $contents} instead
	 */
	public function getContents(?int $sizeLimit = self::SIZE_LIMIT): string
	{
		$contents = $this->object->getContent()
					?? throw new ISDOC\RuntimeException('Failed to get contents of the PDF embedded file.');

		if ($sizeLimit !== null && strlen($contents) > $sizeLimit) {
			throw SupplementException::supplementTooLarge($this->getFilename(), strlen($contents), $sizeLimit);
		}

		return $contents;
	}

	/** {@inheritDoc} */
	public function isOk(): bool
	{
		return true;
	}

}