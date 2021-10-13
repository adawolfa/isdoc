<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Doctrine\Common\Annotations\Annotation;

/**
 * Annotates elements to properties or top-most element in a collection.
 *
 * @Annotation
 * @Annotation\Target({"PROPERTY", "CLASS"})
 */
final class Map
{

	private string $value;

	public function __construct(array $values)
	{
		$this->value = $values['value'];
	}

	public function getValue(): string
	{
		return $this->value;
	}

}