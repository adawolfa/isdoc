<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\X;

use ZipArchive;

/**
 * Helpers for reading ZIP archive entries safely.
 *
 * The uncompressed size of an entry is recorded in the central directory, so it can be read with
 * {@see ZipArchive::statName()} without inflating a single byte. This is the cheap pre-check that lets the
 * reader reject a decompression bomb before {@see ZipArchive::getFromName()} materialises it in memory.
 *
 * @internal
 */
final class Zip
{

	/**
	 * Uncompressed size of an archive entry from the central directory (no decompression), or null when the
	 * entry is absent. The name is matched case-sensitively, matching {@see ZipArchive::getFromName()}.
	 */
	public static function entrySize(ZipArchive $zip, string $name): ?int
	{
		$stat = $zip->statName($name);

		return $stat === false ? null : $stat['size'];
	}

}