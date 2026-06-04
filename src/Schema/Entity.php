<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema;

use Adawolfa\ISDOC\XML\Node;

/**
 * Contract for a schema entity: a typed view backed by an XML {@see Node}.
 *
 * The backing node is public — the supported escape hatch for reading and writing unmapped content (extension
 * elements, non-standard attributes the typed hooks don't cover) — and writable so the engine can bind a
 * parsed element onto a freshly allocated view. {@see Node::bind()} is what does that binding; the default
 * implementation is supplied by the {@see Backing} trait.
 */
interface Entity
{

	public Node $node {
		get;
		set;
	}

}