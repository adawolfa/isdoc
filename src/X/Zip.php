<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\X;

use ZipArchive;

/** @internal */
final class Zip
{

	public static function entrySize(ZipArchive $zip, string $name): ?int
	{
		$stat = $zip->statName($name);

		return $stat === false ? null : $stat['size'];
	}

}
