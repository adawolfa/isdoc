<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

/**
 * VAT subject code for local reverse charge mode.
 */
enum LocalReverseChargeCode: string
{

	/** delivery of gold. */
	case DeliveryOfGold = '1';

	/** trade with emission allowances. */
	case TradeWithEmissionAllowances = '2';

	/** delivery of developer or assembly work. */
	case DeliveryOfDeveloperOrAssemblyWork = '4';

	/** waste (see appendix 5 of VAT bill). */
	case WasteSeeAppendix5OfVATBill = '5';

}