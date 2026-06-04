<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Attachment digest method identification.
 */
class DigestMethod implements Entity
{

	use Backing;

	/** Algorithm identifiers are defined in http://www.w3.org/TR/xmldsig-core/#sec-AlgID. */
	public string $algorithm {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('@Algorithm');
		set {
			$this->node->setString('@Algorithm', $value);
		}
	}

	public function __construct(
		string $algorithm,
	)
	{
		$this->algorithm = $algorithm;
	}

}