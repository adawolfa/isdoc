<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;

final class EnumerationRestrictionException extends RuntimeException
{

	public function __construct(array $options)
	{
		$list = implode(', ', array_map(fn($value): string => var_export($value, true), $options));
		parent::__construct("Value must be one of $list.");
	}

}