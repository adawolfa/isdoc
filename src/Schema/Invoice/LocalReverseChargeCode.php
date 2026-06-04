<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * VAT subject code for local reverse charge mode.
 *
 * Forward-compatible counterpart of the LocalReverseChargeCode enum introduced in 2.0. Reference these
 * constants instead of the deprecated LocalReverseCharge::LOCAL_REVERSE_CHARGE_CODE_* constants to keep the
 * upgrade seamless.
 */
final class LocalReverseChargeCode
{

	public const string DeliveryOfGold = '1';
	public const string TradeWithEmissionAllowances = '2';
	public const string DeliveryOfDeveloperOrAssemblyWork = '4';
	public const string WasteSeeAppendix5OfVATBill = '5';

	private function __construct()
	{
	}

}
