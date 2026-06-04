<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Extension;
use Adawolfa\ISDOC\XML\Node;

/**
 * Arbitrary fragment of user-defined elements. Elements must use their own namespace.
 */
class Extensions implements Extension
{

	private ?Node $isdocNode = null;

	public Node $node {
		get => $this->isdocNode ??= Node::entity(static::class);
		set {
			$this->isdocNode = $value
				->withOrder(Node::orderFor(static::class))
				->withNamespace(Node::namespaceFor(static::class), Node::prefixFor(static::class));
		}
	}

	/**
	 * @template T of Extension
	 * @param class-string<T> $class
	 * @return T
	 */
	public function as(string $class): Extension
	{
		return $this->node->bind($class);
	}

}