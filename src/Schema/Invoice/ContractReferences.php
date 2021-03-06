<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Related contracts.
 *
 * @extends Collection<Contract>
 */
#[Map('ContractReference', Contract::class)]
class ContractReferences extends Collection
{

	/** @return ArrayIterator|Contract[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(Contract $contract): self
	{
		$this->items[] = $contract;
		return $this;
	}

}