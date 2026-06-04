<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa\ISDOC\LengthRestrictionException;
use Adawolfa\ISDOC\LogicException;
use Adawolfa\ISDOC\PatternRestrictionException;
use Adawolfa\ISDOC\Restriction;
use PHPUnit\Framework\TestCase;

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
		$this->expectException(LogicException::class);
		Restriction::pattern('', '~');
	}

}