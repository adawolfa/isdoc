<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\XML;

use Adawolfa\ISDOC;

/**
 * XML access error tagged with the document path of the offending value.
 *
 * Thrown lazily, at the moment a malformed or missing-required value is read, so that an error in one
 * part of the document never blocks reading the parts the caller actually touches.
 */
final class Exception extends ISDOC\Exception
{

	public static function missingValue(string $path): self
	{
		return new self("Missing required value '$path'.");
	}

	public static function malformedValue(string $path, string $value, string $expected): self
	{
		return new self("Value '$path' ('$value') is not a valid $expected.");
	}

	public static function unresolvedReference(string $path, string $ref): self
	{
		return new self("Reference '$path' points to unknown id '$ref'.");
	}

}