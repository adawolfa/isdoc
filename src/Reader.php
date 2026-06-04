<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

/**
 * ISDOC reader.
 */
final class Reader
{

	private Decoder $decoder;

	private X\Reader $xReader;

	private ?PDF\Reader $pdfReader;

	public function __construct(Decoder $decoder, X\Reader $xReader, ?PDF\Reader $pdfReader = null)
	{
		$this->decoder   = $decoder;
		$this->xReader   = $xReader;
		$this->pdfReader = $pdfReader;
	}

	/**
	 * @template T of Schema\Invoice
	 * @param class-string<T> $class
	 * @return T&Schema\Invoice
	 * @throws ReaderException
	 */
	public function file(
		string  $filename,
		string  $class = Schema\Invoice::class,
		?Format $format = null,
	): Schema\Invoice
	{
		$format ??= Utils::detectFormat($filename);

		if ($format === Format::ISDOCX) {
			return $this->xReader->file($filename, $class);
		}

		if ($format === Format::PDF) {

			if ($this->pdfReader === null) {
				throw ReaderException::pdfParsingNotAvailable();
			}

			return $this->pdfReader->file($filename, $class);

		}

		$xml = @file_get_contents($filename);

		if ($xml === false) {
			throw ReaderException::fileCouldNotOpen($filename);
		}

		try {
			return $this->decoder->decode($xml, $class);
		} catch (DecoderException $exception) {
			throw ReaderException::decodeFailureFile($filename, $exception);
		}
	}

	/**
	 * @template T of Schema\Invoice
	 * @param class-string<T> $class
	 * @return T&Schema\Invoice
	 * @throws ReaderException
	 */
	public function xml(
		string $xml,
		string $class = Schema\Invoice::class,
	): Schema\Invoice
	{
		try {
			return $this->decoder->decode($xml, $class);
		} catch (DecoderException $exception) {
			throw ReaderException::decodeFailure($exception);
		}
	}

}