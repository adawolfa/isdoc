<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Smalot\PdfParser\Parser;

/**
 * ISDOC manager — the composition root and the only public entry point for wiring the reader and writer.
 */
final readonly class Manager
{

	private function __construct(
		public Reader $reader,
		public Writer $writer,
	)
	{
	}

	public static function create(): self
	{
		$decoder = new Decoder();
		$encoder = new Encoder();

		$xReader = new X\Reader($decoder);
		$xWriter = new X\Writer($encoder);

		$pdfWriter = new PDF\Writer($encoder);
		$pdfReader = class_exists(Parser::class) ? new PDF\Reader($decoder, new Parser()) : null;

		$reader = new Reader($decoder, $xReader, $pdfReader);
		$writer = new Writer($encoder, $xWriter, $pdfWriter);

		return new self($reader, $writer);
	}

}