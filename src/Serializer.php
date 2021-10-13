<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Adawolfa\ISDOC\Reflection\MappedProperty;
use Adawolfa\ISDOC\Reflection\Property;
use Adawolfa\ISDOC\Reflection\Reflector;
use DateTimeInterface;

/**
 * Converts instance into an XML encoder compatible array.
 */
final class Serializer
{

	private Reflector $reflector;

	public function __construct(Reflector $reflector)
	{
		$this->reflector = $reflector;
	}

	/** @throws SerializerException */
	public function serialize(object $instance): array
	{
		$instance = $this->reflector->instance($instance);
		$data     = [];

		foreach ($instance->getProperties() as $property) {

			$value = $this->serializeProperty($property);

			if ($value === null) {

				if (!$property->isNullable()) {
					throw new SerializerException('Property is not nullable.');
				}

				continue;

			}

			if ($property instanceof MappedProperty) {
				$data[$property->getMap()] = $value;
			}

			// TODO: Reference properties.

		}

		return $data;
	}

	/**
	 * @return array|string|null
	 * @throws SerializerException
	 */
	private function serializeProperty(Property $property)
	{
		if ($property instanceof MappedProperty) {
			return $this->serializeMappedProperty($property);
		}

		return null;
	}

	/**
	 * @return array|string|null
	 * @throws SerializerException
	 */
	private function serializeMappedProperty(MappedProperty $property)
	{
		switch (true) {

			case $property->isPrimitive() || $property->isDate():
				return $this->serializePrimitiveProperty($property);

			case $property->isCollection():
				return $this->serializeCollectionProperty($property);

			default:

				$value = $property->getValue();

				if ($value === null) {
					return null;
				}

				return $this->serialize($value);

		}
	}

	/** @throws SerializerException */
	private function serializeCollectionProperty(MappedProperty $property): ?array
	{
		$value = $property->getValue();

		if ($value === null) {
			return null;
		}

		if (!$value instanceof Collection) {
			throw new RuntimeException('Value is expected to be an instance of ' . Collection::class . '.');
		}

		$items = [];

		$collectionReflection = $this->reflector->instance($value);

		if (!$collectionReflection instanceof Reflection\Collection) {
			throw new RuntimeException('Reflection is expected to be an instance of ' . Reflection\Collection::class . '.');
		}

		foreach ($value as $item) {
			$items[] = [$collectionReflection->getMap() => $this->serialize($item)];
		}

		return $items;
	}

	private function serializePrimitiveProperty(MappedProperty $property): ?string
	{
		$value = $property->getValue();

		if ($value === null) {
			return null;
		}

		if (is_string($value) || is_int($value) || is_float($value)) {
			return (string) $value;
		}

		if (is_bool($value)) {
			return $value ? 'true' : 'false';
		}

		if ($value instanceof DateTimeInterface) {
			return $value->format('Y-m-d');
		}

		throw new RuntimeException('Unsupported value type.');
	}

}