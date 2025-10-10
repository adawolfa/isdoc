<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\PDF;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Decoder;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice as T;
use Exception;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\PDFObject;

/**
 * PDF ISDOC reader.
 *
 * @internal
 */
final class Reader
{

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
		} catch (Exception $exception) {
			throw ReaderException::pdfParsingFailed($exception);
		}

		$xml         = null;
		$supplements = new T\SupplementsList;

		foreach ($pdf->getObjectsByType('EmbeddedFile') as $object) {

			if (!$object instanceof PDFObject) {
				continue;
			}

			$details = $object->getDetails();
			$length  = $details['Length'] ?? null;

			if ($length === null) {
				continue;
			}

			$contents = $object->getContent();

			if ($contents === null) {
				continue;
			}

			if ($xml === null
				&& $length < (1 << 18) // This is what the XSD itself fits.
				&& str_starts_with($contents, '<?xml')
				&& str_contains($contents, '<Invoice')) {
				$xml = $contents;
				continue;
			}

			$supplements->add(new Supplement($object));

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