<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about a tax rate.
 *
 * @property string      $percent
 * @property string|null $taxScheme
 * @property bool|null   $vatApplicable
 * @property bool|null   $localReverseChargeFlag
 */
class TaxCategory implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Tax rate expressed as a percentage.
	 *
	 * @Map("Percent")
	 */
	private string $percent;

	/**
	 * Information about a tax scheme. The most common values are VAT (Value Added Tax) and TIN (Tax Identification Number).
	 *
	 * @Map("TaxScheme")
	 */
	private ?string $taxScheme = null;

	/**
	 * VAT is applicable.
	 *
	 * @Map("VATApplicable")
	 */
	private ?bool $vatApplicable = null;

	/**
	 * Is tax rate included in a local reverse charge mode?.
	 *
	 * @Map("LocalReverseChargeFlag")
	 */
	private ?bool $localReverseChargeFlag = null;

	public function __construct(string $percent)
	{
		$this->setPercent($percent);
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

	public function getTaxScheme(): ?string
	{
		return $this->taxScheme;
	}

	public function setTaxScheme(?string $taxScheme): self
	{
		$this->taxScheme = $taxScheme;
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

	public function getLocalReverseChargeFlag(): ?bool
	{
		return $this->localReverseChargeFlag;
	}

	public function setLocalReverseChargeFlag(?bool $localReverseChargeFlag): self
	{
		$this->localReverseChargeFlag = $localReverseChargeFlag;
		return $this;
	}

}