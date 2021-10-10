<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;

final class DecimalRestrictionException extends RuntimeException
{

	public function __construct()
	{
		parent::__construct('Value is not a valid decimal number.');
	}

}