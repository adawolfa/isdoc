<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema;

/**
 * Marker for a custom {@code <Extensions>} content type.
 *
 * The ISDOC {@code <Extensions>} block holds arbitrary user-defined elements, which the XSD requires to live in
 * their own XML namespace. An {@see Entity} that implements this interface opts into that handling: the backing
 * {@see Node} reads and writes its child elements in the namespace declared by a
 * {@see XMLNamespace} attribute on the class (defaulting to the ISDOC namespace when none is given). Every other
 * entity stays on the plain ISDOC-only path — this interface is the gate that scopes the namespace machinery to
 * extensions alone.
 *
 * The generated {@see Invoice\Extensions} base implements it; user extension types subclass that base, add their
 * typed property hooks and a {@see XMLNamespace} attribute.
 */
interface Extension extends Entity
{
}