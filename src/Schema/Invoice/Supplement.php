<?php

declare(strict_types=1);
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

	public function getFilename(): string
	{
		return $this->filename;
	}

	public function setFilename(string $filename): self
	{
		$this->filename = $filename;
		return $this;
	}

	public function getDigestMethod(): DigestMethod
	{
		return $this->digestMethod;
	}

	public function setDigestMethod(DigestMethod $digestMethod): self
	{
		$this->digestMethod = $digestMethod;
		return $this;
	}

	public function getDigestValue(): string
	{
		return $this->digestValue;
	}

	public function setDigestValue(string $digestValue): self
	{
		$this->digestValue = $digestValue;
		return $this;
	}

	public function getPreview(): ?bool
	{
		return $this->preview;
	}

	public function setPreview(?bool $preview): self
	{
		$this->preview = $preview;
		return $this;
	}

}