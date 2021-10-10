<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Data;
use Adawolfa\ISDOC;

class Exception extends ISDOC\Exception
{

	public static function missingRequiredChild(string $child, string $path): self
	{
		return new self("Missing '$child' element inside '$path'.");
	}

	public static function duplicateReferenceId(string $id): self
	{
		return new self("Duplicated reference ID '$id'.");
	}

	public static function missingReferenceId(string $path): self
	{
		return new self("Missing @ref in '$path'.");
	}

	public static function referencedElementNotFound(string $id, string $path): self
	{
		return new self("Element '$id' referenced in '$path' not found.");
	}

}