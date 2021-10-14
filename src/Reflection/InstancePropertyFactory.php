<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Reflection;
use ReflectionProperty;

/** @internal */
abstract class InstancePropertyFactory
{

	protected ReflectionProperty $property;

	public function __construct(ReflectionProperty $property)
	{
		$this->property = $property;
	}

	abstract public function create(Instance $instance): Property;

	public function getName(): string
	{
		return $this->property->getName();
	}

}