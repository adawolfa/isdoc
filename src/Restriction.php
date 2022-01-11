<?php

declare(strict_types=1);
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
			throw new RuntimeException("Pattern '$pattern' contains forbidden character '~'.");
		}

		if (!preg_match("~^$pattern$~", $value)) {
			throw new PatternRestrictionException($pattern);
		}
	}

	public static function enumeration(string|int|null $value, array $options): void
	{
		if ($value === null) {
			return;
		}

		if (!in_array($value, $options, true)) {
			throw new EnumerationRestrictionException($options);
		}
	}

	public static function decimal(?string $value): void
	{
		if ($value === null) {
			return;
		}

		if (!preg_match('~^-?(\d*\.\d+|\d+)$~', $value)) {
			throw new DecimalRestrictionException;
		}
	}

}