<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\SimpleContentElement;
use Adawolfa\ISDOC\ToArray;

/**
 * Amount.
 *
 * @property string|null $unitCode
 */
class Quantity extends SimpleContentElement implements Arrayable
{

	use ToArray;

	/** Unit. */
	#[Map('@unitCode')]
	private ?string $unitCode = null;

	/** @deprecated Method accessors are deprecated, use {@see $unitCode} property instead. */
	public function getUnitCode(): ?string
	{
		return $this->unitCode;
	}

	/** @deprecated Method accessors are deprecated, use {@see $unitCode} property instead. */
	public function setUnitCode(?string $unitCode): self
	{
		$this->unitCode = $unitCode;
		return $this;
	}

}