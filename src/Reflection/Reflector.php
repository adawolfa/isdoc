<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Reflection;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\RuntimeException;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;

/**
 * Schema classes introspections.
 *
 * @internal
 */
final class Reflector
{

	/**
	 * @template T of object
	 * @param T&object $instance
	 * @return Instance<T&object>
	 */
	public function instance(object $instance, ?string $name = null): Instance
	{
		$object     = new ReflectionObject($instance);
		$properties = [];

		foreach ($this->getProperties($object) as $reflectionProperty) {

			$map = ($reflectionProperty->getAttributes(Map::class)[0] ?? null)?->newInstance();

			if ($map instanceof Map) {

				if ($map->getValue() === null) {
					throw new RuntimeException('Mapped property must have a value specified in ' . Map::class . ' attribute.');
				}

				$properties[] = new InstanceMappedPropertyFactory($reflectionProperty, $map->getValue());

			}

			$reference = ($reflectionProperty->getAttributes(ISDOC\Reference::class)[0] ?? null)?->newInstance();

			if ($reference instanceof ISDOC\Reference) {
				$properties[] = new InstanceReferencePropertyFactory($reflectionProperty);
			}

		}

		if ($instance instanceof ISDOC\Collection) {

			$map = ($object->getAttributes(Map::class)[0] ?? null)?->newInstance();

			if (!$map instanceof Map) {
				throw new RuntimeException('Collection is required to have ' . Map::class . ' attribute.');
			}

			if ($map->getType() === null) {
				throw new RuntimeException('Collection type must be specified.');
			}

			if ($map->getValue() === null && $name === null) {
				throw new RuntimeException('Collection map name must be specified.');
			}

			// @phpstan-ignore-next-line
			return new Collection($instance, $object, $properties, $map->getValue() ?? $name, $map->getType(), $map->getValue() === null);

		}

		// @phpstan-ignore-next-line
		return new Instance($instance, $object, $properties);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $class
	 * @return Instance<T>
	 */
	public function class(string $class, ?string $name = null): Instance
	{
		try {
			$reflection = new ReflectionClass($class);
			$instance   = $reflection->newInstanceWithoutConstructor();
		} catch (ReflectionException $reflectionException) {
			throw new RuntimeException('Failed to instantiate the class.', 0, $reflectionException);
		}

		return $this->instance($instance, $name);
	}

	/**
	 * @param ReflectionClass<object> $reflection
	 * @return ReflectionProperty[]
	 */
	private function getProperties(ReflectionClass $reflection): array
	{
		$properties = $reflection->getParentClass() === false ? [] : $this->getProperties($reflection->getParentClass());

		foreach ($reflection->getProperties(~ReflectionProperty::IS_STATIC) as $property) {
			$properties[$property->getName()] = $property;
		}

		return $properties;
	}

}