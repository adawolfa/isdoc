<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Node;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Collection of classifiers.
 * @implements IteratorAggregate<int, string>
 */
class EgovClassifiers implements Entity, IteratorAggregate, Countable
{

	use Backing;

	/** @return Generator<int, string> */
	public function getIterator(): Generator
	{
		foreach ($this->node->getChildren('EgovClassifier') as $child) {
			yield $child->text ?? '';
		}
	}

	public function add(string $egovClassifier): self
	{
		$node = Node::create('EgovClassifier');
		$node->text = $egovClassifier;
		$this->node->addChild('EgovClassifier', $node);

		return $this;
	}

	public function count(): int
	{
		return count($this->node->getChildren('EgovClassifier'));
	}

}