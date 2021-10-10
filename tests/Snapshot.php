<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use Nette\Utils\Json;
use DateTimeInterface;

trait Snapshot
{

	/** @param mixed $data */
	public function assertSnapshot(string $name, $data): void
	{
		if (is_array($data)) {
			self::walkArrayDateToString($data);
		}

		$filename = __DIR__ . '/snapshots/' . $name . '.json';
		$current = @file_get_contents($filename);
		$serialized = Json::encode($data, Json::PRETTY);

		if ($current === false) {
			$this->addWarning("Snapshot file '$name' does not exist.");
			file_put_contents($filename, $serialized);
		} else {
			$this->assertSame($current, $serialized);
		}
	}

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