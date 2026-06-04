<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * VAT calculation method (there are two types in the Czech Republic).
 */
enum VATCalculationMethod: int
{

	/** From the bottom. */
	case FromTheBottom = 0;

	/** From the top. */
	case FromTheTop = 1;

}