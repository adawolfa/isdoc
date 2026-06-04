<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * Differentiation between batch and serial number.
 *
 * Forward-compatible counterpart of the BatchOrSerialNumber enum introduced in 2.0. Reference these constants
 * instead of the deprecated StoreBatch::BATCH_OR_SERIAL_NUMBER_* constants to keep the upgrade seamless.
 */
final class BatchOrSerialNumber
{

	public const string Batch = 'B';
	public const string SerialNumber = 'S';

	private function __construct()
	{
	}

}
