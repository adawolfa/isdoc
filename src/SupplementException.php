<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC;
use Throwable;

final class SupplementException extends ISDOC\Exception
{

	private function __construct(string $message, ?Throwable $previous = null)
	{
		parent::__construct($message, 0, $previous);
	}

	public static function zipDoesNotContainFile(string $filename): self
	{
		return new self("ISDOCX file does not contain supplement file '$filename'.");
	}

	public static function couldNotWriteFile(string $source, string $filename): self
	{
		return new self("Failed to save '$source' supplement file into '$filename'.");
	}

	public static function couldNotCreateSupplement(string $filename): self
	{
		return new self("Failed to create supplement '$filename'.");
	}

	public static function couldNotComputeSha1(string $filename, string $path): self
	{
		return new self("Could not compute SHA1 for '$filename' from '$path'.");
	}

	public static function unsupportedDigestAlgo(string $filename, string $algo): self
	{
		return new self("Supplement '$filename' uses an unsupported digest algorithm '$algo'.");
	}

	public static function malformedDigest(Throwable $previous): self
	{
		return new self('Could not read supplement digest data.', $previous);
	}

}