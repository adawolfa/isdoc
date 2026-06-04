<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * VAT calculation method (there are two types in the Czech Republic).
 *
 * Forward-compatible counterpart of the VATCalculationMethod enum introduced in 2.0. Reference these constants
 * instead of the deprecated ClassifiedTaxCategory::VAT_CALCULATION_METHOD_* constants to keep the upgrade
 * seamless.
 */
final class VATCalculationMethod
{

	public const int FromTheBottom = 0;
	public const int FromTheTop = 1;

	private function __construct()
	{
	}

}
