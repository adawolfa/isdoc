<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Nette\SmartObject;

/**
 * @property string $content
 */
abstract class SimpleContentElement
{

	use SmartObject;

	protected string $content;

	public function getContent(): string
	{
		return $this->content;
	}

	public function setContent(string $content): self
	{
		$this->content = $content;
		return $this;
	}

}