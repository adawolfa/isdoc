<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Throwable;

final class ReaderException extends Exception
{

	private function __construct(string $message, Throwable $throwable = null)
	{
		parent::__construct($message, 0, $throwable);
	}

	public static function decodeFailure(DecoderException $exception): self
	{
		return new self('ISDOC XML could not be decoded.', $exception);
	}

	public static function decodeFailureFile(string $filename, DecoderException $exception): self
	{
		return new self("File '$filename' could not be decoded.", $exception);
	}

	public static function fileCouldNotOpen(string $filename): self
	{
		return new self("Could not open file '$filename'.");
	}

	public static function zipCouldNotOpen(string $filename): self
	{
		return new self("Could not open ISDOCX file '$filename'.");
	}

	public static function zipCouldNotFindISDOC(string $filename): self
	{
		return new self("Could not find ISDOC data within ISDOCX file '$filename'.");
	}

}