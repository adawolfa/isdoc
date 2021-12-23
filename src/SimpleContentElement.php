<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Nette\SmartObject;

/**
 * Simple content element base class.
 *
 * @property string|null $content
 */
abstract class SimpleContentElement
{

	use SmartObject;

	protected ?string $content = null;

	public function getContent(): ?string
	{
		return $this->content;
	}

	public function setContent(?string $content): self
	{
		$this->content = $content;
		return $this;
	}

}