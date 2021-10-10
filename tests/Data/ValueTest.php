<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC\Data;
use Adawolfa\ISDOC\Data;
use Adawolfa\ISDOC\RuntimeException;
use PHPUnit\Framework\TestCase;
use Adawolfa\ISDOC\Data\Value;
use ReflectionObject;

final class ValueTest extends TestCase
{

	private string  $string;
	private int     $int;
	private bool    $bool;
	private ?string $stringNull;
	private ?int    $intNull;
	private ?bool   $boolNull;
	private self    $self;

	public function testPath(): void
	{
		$this->assertSame('data/value', $this->createValue(null)->getPath());
	}

	public function testCast(): void
	{
		$reflection = new ReflectionObject($this);

		$string     = $reflection->getProperty('string')->getType();
		$int        = $reflection->getProperty('int')->getType();
		$bool       = $reflection->getProperty('bool')->getType();

		$stringNull = $reflection->getProperty('stringNull')->getType();
		$intNull    = $reflection->getProperty('intNull')->getType();
		$boolNull   = $reflection->getProperty('boolNull')->getType();

		$this->assertSame('123', $this->createValue(123)->cast($string));
		$this->assertSame(123, $this->createValue('123')->cast($int));
		$this->assertSame(true, $this->createValue(123)->cast($bool));
		$this->assertSame(false, $this->createValue(0)->cast($bool));

		$this->assertNull($this->createValue(null)->cast($stringNull));
		$this->assertNull($this->createValue(null)->cast($intNull));
		$this->assertNull($this->createValue(null)->cast($boolNull));
	}

	public function testCastMissingValue(): void
	{
		$this->expectException(Data\ValueException::class);
		$reflection = new ReflectionObject($this);
		$string     = $reflection->getProperty('string')->getType();
		$this->createValue(null)->cast($string);
	}

	public function testToString(): void
	{
		$this->assertSame('string', $this->createValue('string')->toString());
		$this->assertNull($this->createValue(null)->toString());
	}

	public function testToDate(): void
	{
		$this->assertSame('2020-06-18 00:00:00', $this->createValue('2020-06-18')->toDate()->format('Y-m-d H:i:s'));
	}

	public function testToDateNull(): void
	{
		$this->assertNull($this->createValue('')->toDate());
	}

	public function testDateIncorrectFormat(): void
	{
		$this->expectException(Data\ValueException::class);
		$this->createValue('foo')->toDate();
	}

	public function testCannotCast(): void
	{
		$this->expectException(Data\ValueException::class);
		$this->createValue((object) [])->toString();
	}

	public function testCastNonPrimitive(): void
	{
		$this->expectException(RuntimeException::class);
		$this->createValue(null)->cast((new ReflectionObject($this))->getProperty('self')->getType());
	}

	/** @param mixed|null $value */
	private function createValue($value): Value
	{
		$data = Data::create(['data' => ['value' => $value]]);
		return new Value($value, $data->getChild('data'), 'value');
	}

}