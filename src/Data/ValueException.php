<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Data;

class ValueException extends Exception
{

	public static function missing(Value $value): self
	{
		return new self("Value {$value->getPath()} is missing.");
	}

	/** @param mixed $originalValue */
	public static function cannotCast(Value $value, $originalValue, string $desiredType): self
	{
		$originalType = is_object($originalValue) ? get_class($originalValue) : gettype($originalValue);
		return new self("Value {$value->getPath()} ($originalType) cannot be casted to $desiredType.");
	}

	public static function invalidDateFormat(Value $value): self
	{
		return new self("Value {$value->getPath()} has incorrect date format.");
	}

}