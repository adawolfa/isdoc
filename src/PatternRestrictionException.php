<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;

final class PatternRestrictionException extends RuntimeException
{

	public function __construct(string $pattern)
	{
		parent::__construct("Value does not match the pattern '$pattern'.");
	}

}