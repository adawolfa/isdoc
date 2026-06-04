<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\PDF;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Decoder;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice as T;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\PDFObject;
use Throwable;

/**
 * PDF ISDOC reader.
 *
 * @internal
 */
final class Reader
{

	/**
	 * Declared-size cap for a single embedded file, rejected before {@see PDFObject::getContent()} decompresses it
	 * and {@see Supplement} computes its digest. Defaults to the supplement cap ({@see ISDOC\Invoice\RemoteSupplement::SizeLimit},
	 * 32 MB); generous for a real attachment. This bounds the stored stream length — the PDF library still owns
	 * whole-file memory. Raise it for larger embedded files, or set {@code null} to disable the check entirely; a
	 * negative limit is rejected.
	 */
	public ?int $supplementSizeLimit = ISDOC\Invoice\RemoteSupplement::SizeLimit {
		set {
			if ($value !== null && $value < 0) {
				throw new ISDOC\RuntimeException("Supplement size limit must not be negative, got $value.");
			}

			$this->supplementSizeLimit = $value;
		}
	}

	private Decoder $decoder;

	private Parser $parser;

	public function __construct(Decoder $decoder, Parser $parser)
	{
		$this->decoder = $decoder;
		$this->parser  = $parser;
	}

	/**
	 * @template T of ISDOC\Schema\Invoice
	 * @param class-string<T> $class
	 * @return T&ISDOC\Schema\Invoice
	 * @throws ISDOC\ReaderException
	 */
	public function file(string $filename, string $class = ISDOC\Schema\Invoice::class): ISDOC\Schema\Invoice
	{
		try {
			$pdf = $this->parser->parseFile($filename);
		} catch (Throwable $exception) {
			throw ReaderException::pdfParsingFailed($exception);
		}

		$xml         = null;
		$supplements = new T\SupplementsList();
		$limit       = $this->supplementSizeLimit;

		foreach ($pdf->getObjectsByType('EmbeddedFile') as $object) {

			if (!$object instanceof PDFObject) {
				continue;
			}

			$details = $object->getDetails();
			$length  = $details['Length'] ?? null;

			if ($length === null) {
				continue;
			}

			if ($limit !== null && is_numeric($length) && $length > $limit) {
				$name = $details['F'] ?? null;
				throw ReaderException::pdfSupplementTooLarge(
					is_string($name) ? $name : 'unknown',
					(int) $length,
					$limit,
				);
			}

			$contents = $object->getContent();

			if ($contents === null) {
				continue;
			}

			if (
				$xml === null
				&& $length < (1 << 18) // This is what the XSD itself fits.
				&& str_starts_with($contents, '<?xml')
				&& str_contains($contents, '<Invoice')
			) {
				$xml = $contents;
				continue;
			}

			try {
				$supplements->add(new Supplement($object));
			} catch (ISDOC\SupplementException $exception) {
				throw ReaderException::pdfCouldNotCreateSupplement($exception);
			}

		}

		if ($xml === null) {
			throw ISDOC\ReaderException::pdfNoIsdocFound();
		}

		try {
			$invoice = $this->decoder->decode($xml, $class);
		} catch (ISDOC\DecoderException $exception) {
			throw ISDOC\ReaderException::decodeFailureFile($filename, $exception);
		}

		try {
			$supplements->add(ISDOC\Invoice\Supplement::fromPath($filename));
			$invoice->supplementsList = $supplements;
		} catch (ISDOC\SupplementException $exception) {
			throw ReaderException::pdfCouldNotCreateSupplement($exception);
		}

		return $invoice;
	}

}