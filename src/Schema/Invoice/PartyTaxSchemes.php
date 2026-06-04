<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Node;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Information about a party's tax scheme.
 * @implements IteratorAggregate<int, PartyTaxScheme>
 */
class PartyTaxSchemes implements Entity, IteratorAggregate, Countable
{

	private ?Node $isdocNode = null;

	public Node $node {
		get => $this->isdocNode ??= Node::entity(self::class);
		set { $this->isdocNode = $value; }
	}

	/** @return Generator<int, PartyTaxScheme> */
	public function getIterator(): Generator
	{
		yield from $this->node->getChildren('PartyTaxScheme', PartyTaxScheme::class);
	}

	public function add(PartyTaxScheme $partyTaxScheme): self
	{
		$this->node->addChild('PartyTaxScheme', $partyTaxScheme);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('PartyTaxScheme'));
	}

}