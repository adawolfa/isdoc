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
 * Information about a tax rate.
 *
 * @property string|BcMath\Number $percent
 * @property string|null          $taxScheme
 * @property bool|null            $vatApplicable
 * @property bool|null            $localReverseChargeFlag
 */
class TaxCategory implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Tax rate expressed as a percentage. */
	#[Map('Percent')]
	private string $percent;

	/** Information about a tax scheme. The most common values are VAT (Value Added Tax) and TIN (Tax Identification Number). */
	#[Map('TaxScheme')]
	private ?string $taxScheme = null;

	/** VAT is applicable. */
	#[Map('VATApplicable')]
	private ?bool $vatApplicable = null;

	/** Is tax rate included in a local reverse charge mode?. */
	#[Map('LocalReverseChargeFlag')]
	private ?bool $localReverseChargeFlag = null;

	public function __construct(string|BcMath\Number $percent)
	{
		$this->setPercent($percent);
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

	/** @deprecated Method accessors are deprecated, use {@see $taxScheme} property instead. */
	public function getTaxScheme(): ?string
	{
		return $this->taxScheme;
	}

	/** @deprecated Method accessors are deprecated, use {@see $taxScheme} property instead. */
	public function setTaxScheme(?string $taxScheme): self
	{
		$this->taxScheme = $taxScheme;
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

	/** @deprecated Method accessors are deprecated, use {@see $localReverseChargeFlag} property instead. */
	public function getLocalReverseChargeFlag(): ?bool
	{
		return $this->localReverseChargeFlag;
	}

	/** @deprecated Method accessors are deprecated, use {@see $localReverseChargeFlag} property instead. */
	public function setLocalReverseChargeFlag(?bool $localReverseChargeFlag): self
	{
		$this->localReverseChargeFlag = $localReverseChargeFlag;
		return $this;
	}

}