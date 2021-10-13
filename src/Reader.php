<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;

/**
 * ISDOC reader.
 */
final class Reader
{

	private Decoder  $decoder;
	private X\Reader $xReader;

	public function __construct(Decoder $decoder, X\Reader $xReader)
	{
		$this->decoder = $decoder;
		$this->xReader = $xReader;
	}

	/**
	 * @template T
	 * @param class-string<T> $class
	 * @return T
	 * @throws ReaderException
	 */
	public function file(
		string $filename,
		string $class = Schema\Invoice::class,
		string $format = Manager::FORMAT_AUTO
	): Schema\Invoice
	{
		$format = $format ?? Utils::detectFormat($filename);

		if ($format === Manager::FORMAT_ISDOCX) {
			return $this->xReader->file($filename, $class);
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
	 * @template T
	 * @param class-string<T> $class
	 * @return T
	 * @throws ReaderException
	 */
	public function xml(
		string $xml,
		string $class = Schema\Invoice::class
	): Schema\Invoice
	{
		try {
			return $this->decoder->decode($xml, $class);
		} catch (DecoderException $exception) {
			throw ReaderException::decodeFailure($exception);
		}
	}

}