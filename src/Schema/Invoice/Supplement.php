<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Document attachment.
 */
class Supplement implements Entity
{

	use Backing;

	/** File name and path. */
	public string $filename {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('Filename');
		set {
			$this->node->setString('Filename', $value);
		}
	}

	/** Attachment digest method identification. */
	public DigestMethod $digestMethod {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('DigestMethod', DigestMethod::class);
		set { $this->node->setChild('DigestMethod', $value); }
	}

	/** Attachment digest value. */
	public string $digestValue {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('DigestValue');
		set {
			$this->node->setString('DigestValue', $value);
		}
	}

	/** Is this attachment document preview. */
	public ?bool $preview {
		/** @throws Exception */
		get => $this->node->getBool('@preview');
		set {
			$this->node->setBool('@preview', $value);
		}
	}

	public function __construct(
		string $filename,
		DigestMethod $digestMethod,
		string $digestValue,
	)
	{
		$this->filename = $filename;
		$this->digestMethod = $digestMethod;
		$this->digestValue = $digestValue;
	}

}