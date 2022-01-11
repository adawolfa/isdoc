<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Reference;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Line reference to an original document which is being corrected by this document (only for document types 2, 3 and 6).
 *
 * @property OriginalDocument $originalDocument
 * @property string|null      $lineID
 */
class OriginalDocumentLine implements Arrayable
{

	use SmartObject;
	use ToArray;

	#[Reference]
	private OriginalDocument $originalDocument;

	/** Line number. */
	#[Map('LineID')]
	private ?string $lineID = null;

	public function __construct(OriginalDocument $originalDocument)
	{
		$this->setOriginalDocument($originalDocument);
	}

	public function getOriginalDocument(): OriginalDocument
	{
		return $this->originalDocument;
	}

	public function setOriginalDocument(OriginalDocument $originalDocument): self
	{
		$this->originalDocument = $originalDocument;
		return $this;
	}

	public function getLineID(): ?string
	{
		return $this->lineID;
	}

	public function setLineID(?string $lineID): self
	{
		$this->lineID = $lineID;
		return $this;
	}

}