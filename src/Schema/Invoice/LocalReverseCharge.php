<?php

declare(strict_types=1);
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

	public const LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_GOLD = '1';
	public const LOCAL_REVERSE_CHARGE_CODE_TRADE_WITH_EMISSION_ALLOWANCES = '2';
	public const LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_DEVELOPER_OR_ASSEMBLY_WORK = '4';
	public const LOCAL_REVERSE_CHARGE_CODE_WASTE_SEE_APPENDIX_5_OF_VAT_BILL = '5';

	/**
	 * VAT subject code for local reverse charge mode.
	 *
	 * @Map("LocalReverseChargeCode")
	 */
	private string $localReverseChargeCode;

	/**
	 * Amount.
	 *
	 * @Map("LocalReverseChargeQuantity")
	 */
	private ?Quantity $localReverseChargeQuantity = null;

	public function __construct(string $localReverseChargeCode)
	{
		$this->setLocalReverseChargeCode($localReverseChargeCode);
	}

	public function getLocalReverseChargeCode(): string
	{
		return $this->localReverseChargeCode;
	}

	public function setLocalReverseChargeCode(string $localReverseChargeCode): self
	{
		Restriction::enumeration($localReverseChargeCode, [
			self::LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_GOLD,
			self::LOCAL_REVERSE_CHARGE_CODE_TRADE_WITH_EMISSION_ALLOWANCES,
			self::LOCAL_REVERSE_CHARGE_CODE_DELIVERY_OF_DEVELOPER_OR_ASSEMBLY_WORK,
			self::LOCAL_REVERSE_CHARGE_CODE_WASTE_SEE_APPENDIX_5_OF_VAT_BILL,
		]);
		$this->localReverseChargeCode = $localReverseChargeCode;
		return $this;
	}

	public function getLocalReverseChargeQuantity(): ?Quantity
	{
		return $this->localReverseChargeQuantity;
	}

	public function setLocalReverseChargeQuantity(?Quantity $localReverseChargeQuantity): self
	{
		$this->localReverseChargeQuantity = $localReverseChargeQuantity;
		return $this;
	}

}