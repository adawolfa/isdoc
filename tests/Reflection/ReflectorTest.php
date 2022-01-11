<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC\Reflection;
use Adawolfa\ISDOC\Reflection\Collection;
use Adawolfa\ISDOC\Reflection\Reflector;
use PHPUnit\Framework\TestCase;

final class ReflectorTest extends TestCase
{

	public function testCollection(): void
	{
		$reflector  = new Reflector();

		/** @var $reflection Collection */
		$reflection = $reflector->class(TestCollection::class);

		$this->assertInstanceOf(Collection::class, $reflection);
		$this->assertSame('TestItem', $reflection->getMap());
		$this->assertSame(TestCollectionItem::class, $reflection->getType());
	}

}