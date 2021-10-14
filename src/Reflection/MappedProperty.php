<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Reflection;
use ReflectionProperty;

/**
 * Mapped element property reflection.
 *
 * @internal
 */
class MappedProperty extends Property
{

	private string $map;

	public function __construct(Instance $instance, ReflectionProperty $property, string $map)
	{
		parent::__construct($instance, $property);
		$this->map = $map;
	}

	public function getMap(): string
	{
		return $this->map;
	}

}