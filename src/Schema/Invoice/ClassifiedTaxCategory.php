<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Compound VAT field.
 *
 * @property string                  $percent
 * @property int                     $vatCalculationMethod
 * @property bool|null               $vatApplicable
 * @property LocalReverseCharge|null $localReverseCharge
 */
class ClassifiedTaxCategory implements Arrayable
{

	use SmartObject;
	use ToArray;

	public const VAT_CALCULATION_METHOD_FROM_THE_BOTTOM = 0;
	public const VAT_CALCULATION_METHOD_FROM_THE_TOP    = 1;

	/**
	 * Percentage VAT rate.
	 *
	 * @Map("Percent")
	 */
	private string $percent;

	/**
	 * VAT calculation method (there are two types in the Czech Republic).
	 *
	 * @Map("VATCalculationMethod")
	 */
	private int $vatCalculationMethod;

	/**
	 * VAT is applicable.
	 *
	 * @Map("VATApplicable")
	 */
	private ?bool $vatApplicable = null;

	/**
	 * Local reverse charge mode.
	 *
	 * @Map("LocalReverseCharge")
	 */
	private ?LocalReverseCharge $localReverseCharge = null;

	public function __construct(string $percent, int $vatCalculationMethod)
	{
		$this->setPercent($percent);
		$this->setVatCalculationMethod($vatCalculationMethod);
	}

	public function getPercent(): string
	{
		return $this->percent;
	}

	public function setPercent(string $percent): self
	{
		Restriction::decimal($percent);
		$this->percent = $percent;
		return $this;
	}

	public function getVatCalculationMethod(): int
	{
		return $this->vatCalculationMethod;
	}

	public function setVatCalculationMethod(int $vatCalculationMethod): self
	{
		Restriction::enumeration($vatCalculationMethod, [
			self::VAT_CALCULATION_METHOD_FROM_THE_BOTTOM,
			self::VAT_CALCULATION_METHOD_FROM_THE_TOP,
		]);
		$this->vatCalculationMethod = $vatCalculationMethod;
		return $this;
	}

	public function getVatApplicable(): ?bool
	{
		return $this->vatApplicable;
	}

	public function setVatApplicable(?bool $vatApplicable): self
	{
		$this->vatApplicable = $vatApplicable;
		return $this;
	}

	public function getLocalReverseCharge(): ?LocalReverseCharge
	{
		return $this->localReverseCharge;
	}

	public function setLocalReverseCharge(?LocalReverseCharge $localReverseCharge): self
	{
		$this->localReverseCharge = $localReverseCharge;
		return $this;
	}

}