<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use DateTimeInterface;

trait Snapshot
{

	public function assertSnapshot(string $name, string $data): void
	{
		$filename = __DIR__ . '/snapshots/' . $name;
		$current  = @file_get_contents($filename);

		if ($current !== false) {
			$this->assertSame($current, $data);
		} else {
			file_put_contents($filename, $data);
			$this->addToAssertionCount(1);
		}
	}

	/**
	 * @param array<string|int, mixed> $array
	 */
	private static function walkArrayDateToString(array &$array): void
	{
		foreach ($array as $key => $value) {

			if ($value instanceof DateTimeInterface) {
				$array[$key] = $value->format('Y-m-d H:i:s');
			} elseif (is_array($value)) {
				self::walkArrayDateToString($array[$key]);
			}

		}
	}

}