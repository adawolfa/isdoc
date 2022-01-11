<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Attribute;

/**
 * Annotates elements to properties or top-most element in a collection.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class Map
{

	private string  $value;
	private ?string $type;

	public function __construct(string $value, string $type = null)
	{
		$this->value = $value;
		$this->type  = $type;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function getType(): ?string
	{
		return $this->type;
	}

}