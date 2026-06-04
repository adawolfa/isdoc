<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema;

use Adawolfa\ISDOC\XML\Node;

/**
 * Default {@see Entity} implementation mixed into every schema entity.
 *
 * The element tag (the class basename) and child-element order are inferred from the using class by
 * {@see Node::entity()}/{@see Node::orderFor()} — no Element/Order constants are declared. The {@code self::class}
 * the trait reports is the schema class that uses it (not a decorated subclass), so decorated overrides keep the
 * schema's canonical order. The backing node is materialised on first write — so constructors stay plain value
 * assignment and a never-touched entity costs nothing — and bound up front on read. The setter re-views any
 * assigned node with this entity's own child order, so writes land in XSD-sequence position.
 */
trait Backing
{

	public Node $node {
		get => $this->node ??= Node::entity(self::class);
		set { $this->node = $value->withOrder(Node::orderFor(self::class)); }
	}

}