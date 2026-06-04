<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Document attachment.
 *
 * @property string       $filename
 * @property DigestMethod $digestMethod
 * @property string       $digestValue
 * @property bool|null    $preview
 */
class Supplement implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** File name and path. */
	#[Map('Filename')]
	private string $filename;

	/** Attachment digest method identification. */
	#[Map('DigestMethod')]
	private DigestMethod $digestMethod;

	/** Attachment digest value. */
	#[Map('DigestValue')]
	private string $digestValue;

	/** Is this attachment document preview. */
	#[Map('@preview')]
	private ?bool $preview = null;

	public function __construct(string $filename, DigestMethod $digestMethod, string $digestValue)
	{
		$this->setFilename($filename);
		$this->setDigestMethod($digestMethod);
		$this->setDigestValue($digestValue);
	}

	/** @deprecated Method accessors are deprecated, use {@see $filename} property instead. */
	public function getFilename(): string
	{
		return $this->filename;
	}

	/** @deprecated Method accessors are deprecated, use {@see $filename} property instead. */
	public function setFilename(string $filename): self
	{
		$this->filename = $filename;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $digestMethod} property instead. */
	public function getDigestMethod(): DigestMethod
	{
		return $this->digestMethod;
	}

	/** @deprecated Method accessors are deprecated, use {@see $digestMethod} property instead. */
	public function setDigestMethod(DigestMethod $digestMethod): self
	{
		$this->digestMethod = $digestMethod;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $digestValue} property instead. */
	public function getDigestValue(): string
	{
		return $this->digestValue;
	}

	/** @deprecated Method accessors are deprecated, use {@see $digestValue} property instead. */
	public function setDigestValue(string $digestValue): self
	{
		$this->digestValue = $digestValue;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $preview} property instead. */
	public function getPreview(): ?bool
	{
		return $this->preview;
	}

	/** @deprecated Method accessors are deprecated, use {@see $preview} property instead. */
	public function setPreview(?bool $preview): self
	{
		$this->preview = $preview;
		return $this;
	}

}