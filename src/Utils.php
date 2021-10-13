<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;

final class Utils
{

	public static function detectFormat(string $filename): string
	{
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		switch ($extension) {

			case Manager::FORMAT_ISDOC:
			case Manager::FORMAT_ISDOCX:
				return $extension;

			default: return Manager::FORMAT_ISDOC;

		}
	}

}