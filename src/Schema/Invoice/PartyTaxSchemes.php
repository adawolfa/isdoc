<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Collection of party's tax schemes.
 *
 * @extends Collection<PartyTaxScheme>
 */
#[Map(null, PartyTaxScheme::class)]
class PartyTaxSchemes extends Collection
{

	/** @return ArrayIterator<int, PartyTaxScheme> */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(PartyTaxScheme $partyTaxScheme): self
	{
		$this->items[] = $partyTaxScheme;
		return $this;
	}

}