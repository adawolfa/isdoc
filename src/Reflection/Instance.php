<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Reflection;
use Adawolfa\ISDOC\RuntimeException;
use ReflectionObject;
use ReflectionProperty;
use ReflectionException;

/**
 * @template T
 */
class Instance
{

	/** @var T */
	private object $instance;

	private ReflectionObject $reflection;

	/** @var Property[] */
	private array $properties = [];

	/**
	 * @param T                         $instance
	 * @param InstancePropertyFactory[] $properties
	 */
	public function __construct(
		object           $instance,
		ReflectionObject $reflection,
		array            $properties
	)
	{
		$this->instance   = $instance;
		$this->reflection = $reflection;

		foreach ($properties as $factory) {
			$this->properties[$factory->getName()] = $factory->create($this);
		}
	}

	/** @return T */
	public function getInstance(): object
	{
		return $this->instance;
	}

	/** @return Property[] */
	public function getProperties(): array
	{
		return $this->properties;
	}

	public function getReflection(): ReflectionObject
	{
		return $this->reflection;
	}

}