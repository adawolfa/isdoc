<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * Differentiation between batch and serial number.
 */
enum BatchOrSerialNumber: string
{

	/** Batch. */
	case Batch = 'B';

	/** Serial number. */
	case SerialNumber = 'S';

}