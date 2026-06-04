<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use BcMath;

/**
 * Runtime deprecation notices emitted from the generated schema accessors.
 */
final class Deprecations
{

	/**
	 * Decimal and monetary values become BcMath\Number instances in 2.0. Once the runtime provides the
	 * class, passing such a value as a plain string through a setter is deprecated.
	 */
	public static function number(string|BcMath\Number|null $value): void
	{
		if (is_string($value) && class_exists('BcMath\Number', false)) {
			trigger_deprecation(
				'adawolfa/isdoc',
				'1.6',
				'Passing decimal values as string is deprecated, use BcMath\Number instead.',
			);
		}
	}

}