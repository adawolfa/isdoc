<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema;

use Attribute;

/**
 * Declares the XML namespace the child elements of an {@see Extension} type live in.
 *
 * Placed on a custom extension class; read once (and cached) by {@see Node::namespaceFor()}.
 * With no {@see $prefix} the elements serialise with a default-namespace declaration ({@code xmlns="…"}); with a
 * prefix they serialise prefixed ({@code <ext:Name xmlns:ext="…">}). Both forms are schema-equivalent — the prefix
 * is purely cosmetic.
 *
 * The class is named {@code XMLNamespace} rather than {@code Namespace} because the latter is a reserved word in
 * PHP; the acronym stays upper-case per the library's convention.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class XMLNamespace
{

	public function __construct(
		public string  $uri,
		public ?string $prefix = null,
	)
	{
	}

}