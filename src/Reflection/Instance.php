<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Reflection;

use ReflectionObject;

/**
 * Element instance reflection.
 *
 * @template T of object
 * @internal
 */
class Instance
{

	/** @var T&object */
	private object $instance;

	private ReflectionObject $reflection;

	/** @var array<string, Property<object>> */
	private array $properties = [];

	/**
	 * @param T&object                     $instance
	 * @param ReflectionObject             $reflection
	 * @param InstancePropertyFactory<T>[] $properties
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

	/** @return T&object */
	public function getInstance(): object
	{
		return $this->instance;
	}

	/** @return Property<object>[] */
	public function getProperties(): array
	{
		return $this->properties;
	}

	public function getReflection(): ReflectionObject
	{
		return $this->reflection;
	}

}