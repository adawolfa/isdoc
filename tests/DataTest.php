<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use Adawolfa\ISDOC\RuntimeException;
use PHPUnit\Framework\TestCase;
use Adawolfa\ISDOC\Data;

final class DataTest extends TestCase
{

	public function testGetValue(): void
	{
		$data = Data::create(['value' => 'string']);
		$this->assertTrue($data->hasValue('value'));
		$value = $data->getValue('value');
		$this->assertSame('string', $value->toString());
	}

	public function testNonExistentValue(): void
	{
		$data = Data::create([]);
		$this->assertFalse($data->hasValue('value'));
		$value = $data->getValue('value');
		$this->assertNull($value->toString());
	}

	public function testGetChild(): void
	{
		$data = Data::create(['child' => ['value' => 'string']]);
		$this->assertTrue($data->hasChild('child'));
		$child = $data->getChild('child');
		$value = $child->getValue('value');
		$this->assertSame('string', $value->toString());
	}

	public function testNonExistentChild(): void
	{
		$this->expectException(RuntimeException::class);
		$data = Data::create([]);
		$this->assertFalse($data->hasChild('child'));
		$data->getChild('child');
	}

	public function testGetChildListFromOne(): void
	{
		$data = Data::create(['item' => ['a' => 'string']]);
		$list = $data->getChildList('item');
		$this->assertCount(1, $list);
		$this->assertSame('string', $list[0]->getValue('a')->toString());
	}

	public function testGetChildListFromMultiple(): void
	{
		$data = Data::create(['item' => [['a' => 'string1'], ['a' => 'string2']]]);
		$list = $data->getChildList('item');
		$this->assertCount(2, $list);
		$this->assertSame('string1', $list[0]->getValue('a')->toString());
		$this->assertSame('string2', $list[1]->getValue('a')->toString());
	}

	public function testNonExistentChildList(): void
	{
		$this->expectException(RuntimeException::class);
		$data = Data::create([]);
		$data->getChildList('child');
	}

	public function testPath(): void
	{
		$data = Data::create(['child' => ['child2' => ['value' => 'string']]]);
		$this->assertSame('', $data->getPath());
		$this->assertSame('child', $data->getChild('child')->getPath());
		$this->assertSame('child/child2', $data->getChild('child')->getChild('child2')->getPath());
	}

}