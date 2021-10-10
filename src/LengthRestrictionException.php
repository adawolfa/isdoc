<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;

final class LengthRestrictionException extends RuntimeException
{

	private function __construct(string $message)
	{
		parent::__construct($message);
	}

	public static function length(int $length): self
	{
		return new self("Value must be $length characters long.");
	}

	public static function maxLength(int $maxLength): self
	{
		return new self("Value can only be up to $maxLength characters long.");
	}

}