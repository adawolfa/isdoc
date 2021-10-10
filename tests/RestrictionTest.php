<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use Adawolfa\ISDOC\DecimalRestrictionException;
use Adawolfa\ISDOC\EnumerationRestrictionException;
use Adawolfa\ISDOC\LengthRestrictionException;
use Adawolfa\ISDOC\PatternRestrictionException;
use Adawolfa\ISDOC\RuntimeException;
use PHPUnit\Framework\TestCase;
use Adawolfa\ISDOC\Restriction;

final class RestrictionTest extends TestCase
{

	public function testLength(): void
	{
		Restriction::length('abc', 3);
		Restriction::length(null, 3);
		$this->expectException(LengthRestrictionException::class);
		Restriction::length('abcd', 3);
	}

	public function testMaxLength(): void
	{
		Restriction::maxLength('ab', 3);
		Restriction::maxLength('abc', 3);
		Restriction::maxLength(null, 3);
		$this->expectException(LengthRestrictionException::class);
		Restriction::maxLength('abcd', 3);
	}

	public function testPattern(): void
	{
		Restriction::pattern(null, '[a-z]+');
		Restriction::pattern('abc', '[a-z]+');
		$this->expectException(PatternRestrictionException::class);
		Restriction::pattern('123', '[a-z]+');
	}

	public function testPatternForbiddenTilde(): void
	{
		$this->expectException(RuntimeException::class);
		Restriction::pattern('', '~');
	}

	public function testEnumeration(): void
	{
		Restriction::enumeration(null, [1, 2, 3]);
		Restriction::enumeration(1, [1, 2, 3]);
		$this->expectException(EnumerationRestrictionException::class);
		Restriction::enumeration('1', [1, 2, 3]);
	}

	public function testDecimal(): void
	{
		Restriction::decimal(null);
		Restriction::decimal('.000');
		Restriction::decimal('-.000');
		Restriction::decimal('000');
		Restriction::decimal('-000');
		Restriction::decimal('123.456');
		Restriction::decimal('-123.456');
		$this->expectException(DecimalRestrictionException::class);
		Restriction::decimal('1,4');
	}

}