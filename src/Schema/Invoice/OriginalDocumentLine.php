<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Line reference to an original document which is being corrected by this document (only for document types 2, 3 and 6).
 */
class OriginalDocumentLine implements Entity
{

	use Backing;

	private ?OriginalDocument $isdocReferenceOriginalDocument = null;

	public OriginalDocument $originalDocument {
		/** @throws Exception */
		get {
			if ($this->isdocReferenceOriginalDocument !== null) {
				return $this->isdocReferenceOriginalDocument;
			}

			$ref = $this->node->getString('@ref');

			return $ref !== null
				? $this->node->getReference(OriginalDocument::class, $ref)
				: $this->node->view(OriginalDocument::class);
		}
		set {
			$this->isdocReferenceOriginalDocument = $value;
			$this->node->setReference($value);
		}
	}

	/** Line number. */
	public ?string $lineID {
		get => $this->node->getString('LineID');
		set {
			$this->node->setString('LineID', $value);
		}
	}

	public function __construct(
		OriginalDocument $originalDocument,
	)
	{
		$this->originalDocument = $originalDocument;
	}

}