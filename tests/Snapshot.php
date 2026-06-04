<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

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

}