<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;

/** @internal */
final class Utils
{

	public static function detectFormat(string $filename): string
	{
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		return match ($extension) {
			Manager::FORMAT_ISDOCX => $extension,
			default                => Manager::FORMAT_ISDOC,
		};
	}

}