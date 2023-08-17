<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Data;

final class MissingValueException extends ValueException
{

	public static function missing(Value $value): self
	{
		return new self("Value {$value->getPath()} is missing.");
	}

}