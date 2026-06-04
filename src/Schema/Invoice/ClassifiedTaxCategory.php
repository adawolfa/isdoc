<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Deprecations;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use BcMath;
use Nette\SmartObject;

/**
 * Compound VAT field.
 *
 * @property string|BcMath\Number    $percent
 * @property int                     $vatCalculationMethod
 * @property bool|null               $vatApplicable
 * @property LocalReverseCharge|null $localReverseCharge
 */
class ClassifiedTaxCategory implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** @deprecated use {@see VATCalculationMethod::FromTheBottom} instead */
	public const int VAT_CALCULATION_METHOD_FROM_THE_BOTTOM = VATCalculationMethod::FromTheBottom;

	/** @deprecated use {@see VATCalculationMethod::FromTheTop} instead */
	public const int VAT_CALCULATION_METHOD_FROM_THE_TOP = VATCalculationMethod::FromTheTop;

	/** Percentage VAT rate. */
	#[Map('Percent')]
	private string $percent;

	/** VAT calculation method (there are two types in the Czech Republic). */
	#[Map('VATCalculationMethod')]
	private int $vatCalculationMethod;

	/** VAT is applicable. */
	#[Map('VATApplicable')]
	private ?bool $vatApplicable = null;

	/** Local reverse charge mode. */
	#[Map('LocalReverseCharge')]
	private ?LocalReverseCharge $localReverseCharge = null;

	public function __construct(string|BcMath\Number $percent, int $vatCalculationMethod)
	{
		$this->setPercent($percent);
		$this->setVatCalculationMethod($vatCalculationMethod);
	}

	/** @deprecated Method accessors are deprecated, use {@see $percent} property instead. */
	public function getPercent(): string
	{
		return $this->percent;
	}

	/** @deprecated Method accessors are deprecated, use {@see $percent} property instead. */
	public function setPercent(string|BcMath\Number $percent): self
	{
		Deprecations::number($percent);
		$percent = (string) $percent;
		Restriction::decimal($percent);
		$this->percent = $percent;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $vatCalculationMethod} property instead. */
	public function getVatCalculationMethod(): int
	{
		return $this->vatCalculationMethod;
	}

	/** @deprecated Method accessors are deprecated, use {@see $vatCalculationMethod} property instead. */
	public function setVatCalculationMethod(int $vatCalculationMethod): self
	{
		Restriction::enumeration($vatCalculationMethod, [
			VATCalculationMethod::FromTheBottom,
			VATCalculationMethod::FromTheTop,
		]);
		$this->vatCalculationMethod = $vatCalculationMethod;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $vatApplicable} property instead. */
	public function getVatApplicable(): ?bool
	{
		return $this->vatApplicable;
	}

	/** @deprecated Method accessors are deprecated, use {@see $vatApplicable} property instead. */
	public function setVatApplicable(?bool $vatApplicable): self
	{
		$this->vatApplicable = $vatApplicable;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $localReverseCharge} property instead. */
	public function getLocalReverseCharge(): ?LocalReverseCharge
	{
		return $this->localReverseCharge;
	}

	/** @deprecated Method accessors are deprecated, use {@see $localReverseCharge} property instead. */
	public function setLocalReverseCharge(?LocalReverseCharge $localReverseCharge): self
	{
		$this->localReverseCharge = $localReverseCharge;
		return $this;
	}

}