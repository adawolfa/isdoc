<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Reflection;
use ReflectionProperty;

final class InstanceMappedPropertyFactory extends InstancePropertyFactory
{

	private string $map;

	public function __construct(ReflectionProperty $property, string $map)
	{
		parent::__construct($property);
		$this->map = $map;
	}

	public function create(Instance $instance): Property
	{
		return new MappedProperty($instance, $this->property, $this->map);
	}

}