<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Local reverse charge mode.
 *
 * @property string        $localReverseChargeCode
 * @property Quantity|null $localReverseChargeQuantity
 */
class LocalReverseCharge implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** @deprecated use {@see LocalReverseChargeCode::DeliveryOfGold} instead */
	public const string LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_GOLD = LocalReverseChargeCode::DeliveryOfGold;

	/** @deprecated use {@see LocalReverseChargeCode::TradeWithEmissionAllowances} instead */
	public const string LOCAL_REVERSE_CHARGE_CODE_TRADE_WITH_EMISSION_ALLOWANCES = LocalReverseChargeCode::TradeWithEmissionAllowances;

	/** @deprecated use {@see LocalReverseChargeCode::DeliveryOfDeveloperOrAssemblyWork} instead */
	public const string LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_DEVELOPER_OR_ASSEMBLY_WORK = LocalReverseChargeCode::DeliveryOfDeveloperOrAssemblyWork;

	/** @deprecated use {@see LocalReverseChargeCode::WasteSeeAppendix5OfVATBill} instead */
	public const string LOCAL_REVERSE_CHARGE_CODE_WASTE_SEE_APPENDIX_5_OF_VAT_BILL = LocalReverseChargeCode::WasteSeeAppendix5OfVATBill;

	/** VAT subject code for local reverse charge mode. */
	#[Map('LocalReverseChargeCode')]
	private string $localReverseChargeCode;

	/** Amount. */
	#[Map('LocalReverseChargeQuantity')]
	private ?Quantity $localReverseChargeQuantity = null;

	public function __construct(string $localReverseChargeCode)
	{
		$this->setLocalReverseChargeCode($localReverseChargeCode);
	}

	/** @deprecated Method accessors are deprecated, use {@see $localReverseChargeCode} property instead. */
	public function getLocalReverseChargeCode(): string
	{
		return $this->localReverseChargeCode;
	}

	/** @deprecated Method accessors are deprecated, use {@see $localReverseChargeCode} property instead. */
	public function setLocalReverseChargeCode(string $localReverseChargeCode): self
	{
		Restriction::enumeration($localReverseChargeCode, [
			LocalReverseChargeCode::DeliveryOfGold,
			LocalReverseChargeCode::TradeWithEmissionAllowances,
			LocalReverseChargeCode::DeliveryOfDeveloperOrAssemblyWork,
			LocalReverseChargeCode::WasteSeeAppendix5OfVATBill,
		]);
		$this->localReverseChargeCode = $localReverseChargeCode;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $localReverseChargeQuantity} property instead. */
	public function getLocalReverseChargeQuantity(): ?Quantity
	{
		return $this->localReverseChargeQuantity;
	}

	/** @deprecated Method accessors are deprecated, use {@see $localReverseChargeQuantity} property instead. */
	public function setLocalReverseChargeQuantity(?Quantity $localReverseChargeQuantity): self
	{
		$this->localReverseChargeQuantity = $localReverseChargeQuantity;
		return $this;
	}

}