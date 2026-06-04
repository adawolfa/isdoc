<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Related contracts.
 * @implements IteratorAggregate<int, Contract>
 */
class ContractReferences implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, Contract> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('ContractReference', Contract::class);
	}

	public function add(Contract $contractReference): self
	{
		$this->node->addChild('ContractReference', $contractReference);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('ContractReference'));
	}

}