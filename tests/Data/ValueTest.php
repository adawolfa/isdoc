<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC\Data;

use Adawolfa\ISDOC\Data;
use Adawolfa\ISDOC\Data\Value;
use Adawolfa\ISDOC\Data\ValueException;
use Adawolfa\ISDOC\RuntimeException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionNamedType;
use ReflectionObject;

final class ValueTest extends TestCase
{

	private string $string; // @phpstan-ignore-line

	private int $int; // @phpstan-ignore-line

	private bool $bool; // @phpstan-ignore-line

	private ?string $stringNull; // @phpstan-ignore-line

	private ?int $intNull; // @phpstan-ignore-line

	private ?bool $boolNull; // @phpstan-ignore-line

	private self $self; // @phpstan-ignore-line

	public function testPath(): void
	{
		$this->assertSame('data/value', $this->createValue(null)->getPath());
	}

	/**
	 * @throws ValueException
	 */
	public function testCast(): void
	{
		$reflection = new ReflectionObject($this);

		$string = $reflection->getProperty('string')->getType();
		$int    = $reflection->getProperty('int')->getType();
		$bool   = $reflection->getProperty('bool')->getType();

		$stringNull = $reflection->getProperty('stringNull')->getType();
		$intNull    = $reflection->getProperty('intNull')->getType();
		$boolNull   = $reflection->getProperty('boolNull')->getType();

		$this->assertInstanceOf(ReflectionNamedType::class, $string);
		$this->assertInstanceOf(ReflectionNamedType::class, $int);
		$this->assertInstanceOf(ReflectionNamedType::class, $bool);

		$this->assertInstanceOf(ReflectionNamedType::class, $stringNull);
		$this->assertInstanceOf(ReflectionNamedType::class, $intNull);
		$this->assertInstanceOf(ReflectionNamedType::class, $boolNull);

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
		$this->assertInstanceOf(ReflectionNamedType::class, $string);
		$this->createValue(null)->cast($string);
	}

	/**
	 * @throws ValueException
	 */
	public function testToString(): void
	{
		$this->assertSame('string', $this->createValue('string')->toString());
		$this->assertNull($this->createValue(null)->toString());
	}

	/**
	 * @throws ValueException
	 */
	public function testToDate(): void
	{
		$this->assertSame('2020-06-18 00:00:00', $this->createValue('2020-06-18')->toDate()?->format('Y-m-d H:i:s'));
	}

	/**
	 * @throws ValueException
	 */
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

	/**
	 * @throws ReflectionException
	 * @throws ValueException
	 */
	public function testCastNonPrimitive(): void
	{
		$this->expectException(RuntimeException::class);
		$reflection = (new ReflectionObject($this))->getProperty('self')->getType();
		$this->assertInstanceOf(ReflectionNamedType::class, $reflection);
		$this->createValue(null)->cast($reflection);
	}

	private function createValue(mixed $value): Value
	{
		$data = Data::create(['data' => ['value' => $value]]);
		return new Value($value, $data->getChild('data'), 'value');
	}

}