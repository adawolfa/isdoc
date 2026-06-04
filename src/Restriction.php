<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

/**
 * Common validations.
 */
final class Restriction
{

	public static function length(?string $value, int $length): void
	{
		if ($value === null) {
			return;
		}

		if (strlen($value) !== $length) {
			throw LengthRestrictionException::length($length);
		}
	}

	public static function maxLength(?string $value, int $maxLength): void
	{
		if ($value === null) {
			return;
		}

		if (strlen($value) > $maxLength) {
			throw LengthRestrictionException::maxLength($maxLength);
		}
	}

	public static function pattern(?string $value, string $pattern): void
	{
		if ($value === null) {
			return;
		}

		if (str_contains($pattern, '~')) {
			throw new LogicException("Pattern '$pattern' contains forbidden character '~'.");
		}

		if (preg_match("~^$pattern$~", $value) !== 1) {
			throw new PatternRestrictionException($pattern);
		}
	}

}