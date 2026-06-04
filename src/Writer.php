<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

/**
 * ISDOC writer.
 */
final class Writer
{

	private Encoder $encoder;

	private X\Writer $xWriter;

	private PDF\Writer $pdfWriter;

	public function __construct(Encoder $encoder, X\Writer $xWriter, PDF\Writer $pdfWriter)
	{
		$this->encoder   = $encoder;
		$this->xWriter   = $xWriter;
		$this->pdfWriter = $pdfWriter;
	}

	/** @throws WriterException */
	public function file(Schema\Invoice $invoice, string $filename, ?Format $format = null): void
	{
		$format ??= Utils::detectFormat($filename);

		if ($format === Format::ISDOCX) {
			$this->xWriter->file($invoice, $filename);
			return;
		}

		if ($format === Format::PDF) {
			$this->pdfWriter->file($invoice, $filename);
			return;
		}

		try {
			$xml = $this->encoder->encode($invoice);
		} catch (EncoderException $exception) {
			throw WriterException::encodeFailure($exception);
		}

		if (@file_put_contents($filename, $xml) === false) {
			throw WriterException::fileCouldNotWrite($filename);
		}
	}

	/** @throws WriterException */
	public function xml(Schema\Invoice $invoice): string
	{
		try {
			return $this->encoder->encode($invoice);
		} catch (EncoderException $exception) {
			throw WriterException::encodeFailure($exception);
		}
	}

}