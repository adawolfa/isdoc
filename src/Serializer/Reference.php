<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Serializer;

/**
 * Referenced element.
 */
final class Reference
{

	private object $instance;
	private ?ID    $element = null;

	public function __construct(object $instance)
	{
		$this->instance = $instance;
	}

	public function getInstance(): object
	{
		return $this->instance;
	}

	public function setElement(ID $element): void
	{
		$this->element = $element;
	}

	public function getElement(): ?ID
	{
		return $this->element;
	}

}