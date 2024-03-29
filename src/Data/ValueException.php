<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Data;

class ValueException extends Exception
{

	public static function cannotCast(Value $value, mixed $originalValue, string $desiredType): self
	{
		$originalType = is_object($originalValue) ? get_class($originalValue) : gettype($originalValue);
		return new self("Value {$value->getPath()} ($originalType) cannot be casted to $desiredType.");
	}

	public static function invalidDateFormat(Value $value): self
	{
		return new self("Value {$value->getPath()} has incorrect date format.");
	}

}