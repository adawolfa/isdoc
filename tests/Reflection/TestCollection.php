<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC\Reflection;
use Adawolfa\ISDOC;
use ArrayIterator;
use Tests\Adawolfa\ISDOC\Reflection\TestCollectionItem as TCI;

/**
 * @Adawolfa\ISDOC\Map("TestItem")
 * @extends ISDOC\Collection<TCI>
 */
final class TestCollection extends ISDOC\Collection
{

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

}