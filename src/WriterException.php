<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Throwable;

final class WriterException extends Exception
{

	private function __construct(string $message, Throwable $throwable = null)
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

}