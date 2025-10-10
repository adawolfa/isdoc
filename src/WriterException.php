<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC\Invoice\Supplement;
use Throwable;

final class WriterException extends Exception
{

	private function __construct(string $message, ?Throwable $throwable = null)
	{
		parent::__construct($message, 0, $throwable);
	}

	public static function encodeFailure(EncoderException $exception): self
	{
		return new self('Failed to encode invoice to XML.', $exception);
	}

	public static function fileCouldNotWrite(string $filename): self
	{
		return new self("Failed to write file '$filename'.");
	}

	public static function zipCouldNotCreate(string $filename): self
	{
		return new self("Failed to create ISDOCX file '$filename'.");
	}

	public static function failedAddSupplement(string $path, string $filename): self
	{
		return new self("Failed to add supplement '$filename' from '$path'.");
	}

	public static function noPdfSupplement(): self
	{
		return new self('No PDF supplement that the ISDOC could be appended to found in the invoice.');
	}

	public static function pdfAppendFailed(string $message): self
	{
		return new self("Failed to append ISDOC to PDF: $message");
	}

	public static function couldNotCreateTempFile(): self
	{
		return new self('Could not create temporary file.');
	}

	public static function unsupportedSupplementType(): self
	{
		return new self('Supplement must be of type ' . Supplement::class . '.');
	}

}