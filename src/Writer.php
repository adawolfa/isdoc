<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;

/**
 * ISDOC writer.
 */
final class Writer
{

	private Encoder  $encoder;
	private X\Writer $xWriter;

	public function __construct(Encoder $encoder, X\Writer $xWriter)
	{
		$this->encoder = $encoder;
		$this->xWriter = $xWriter;
	}

	/** @throws WriterException */
	public function file(Schema\Invoice $invoice, string $filename, string $format = Manager::FORMAT_AUTO): void
	{
		$format = $format ?? Utils::detectFormat($filename);

		if ($format === Manager::FORMAT_ISDOCX) {
			$this->xWriter->file($invoice, $filename);
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