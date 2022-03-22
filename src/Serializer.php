<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Adawolfa\ISDOC\Reflection\MappedProperty;
use Adawolfa\ISDOC\Reflection\Property;
use Adawolfa\ISDOC\Reflection\ReferenceProperty;
use Adawolfa\ISDOC\Reflection\Reflector;
use DateTimeInterface;

/**
 * Converts instance into an XML encoder compatible array.
 *
 * @internal
 */
final class Serializer
{

	private Reflector $reflector;
	private int       $depth = 0;

	public function __construct(Reflector $reflector)
	{
		$this->reflector = $reflector;
	}

	/** @throws SerializerException */
	public function serialize(object $instance): array
	{
		$this->depth++;

		try {

			$instanceReflection = $this->reflector->instance($instance);
			$data               = ['@id' => new Serializer\ID($instanceReflection->getInstance())];

			foreach ($instanceReflection->getProperties() as $property) {

				if ($property instanceof ReferenceProperty && $property->getValue() !== null) {

					$referencedElement = $property->getValue();

					if (!is_object($referencedElement)) {
						throw new RuntimeException('Referenced element must be an object.');
					}

					$data['@ref'] = new Serializer\Reference($referencedElement);
					continue;

				}

				$value = $this->serializeProperty($property);

				if ($value === null) {

					if (!$property->isNullable()) {
						throw SerializerException::propertyNotNullable($property);
					}

					continue;

				}

				if ($property instanceof MappedProperty) {
					$data[$property->getMap()] = $value;
				}

			}

			if ($instance instanceof SimpleContentElement && $instance->content !== null) {
				$data['#'] = $instance->content;
			}

			if ($this->depth === 1) {
				$this->resolveReferences($data);
			}

			return $data;

		} finally {
			$this->depth--;
		}
	}

	/** @throws SerializerException */
	private function resolveReferences(array &$data): void
	{
		/** @var $elements Serializer\ID[] */
		$elements = [];

		/** @var $references Serializer\Reference[] */
		$references = [];

		array_walk_recursive($data, function($value) use(&$elements, &$references): void {

			switch (true) {

				case $value instanceof Serializer\ID:
					$elements[] = $value;
					break;

				case $value instanceof Serializer\Reference:
					$references[] = $value;
					break;

			}

		});

		$id = 1;

		foreach ($elements as $element) {

			foreach ($references as $reference) {

				if ($reference->getInstance() !== $element->getInstance()) {
					continue;
				}

				if ($element->getId() === null) {
					$element->setId($id++);
				}

				$reference->setElement($element);

			}

		}

		$data = self::mapRecursive($data, function($value) {

			switch (true) {

				case $value instanceof Serializer\Reference:

					if ($value->getElement() === null) {
						throw SerializerException::referencedElementNotFound(get_class($value->getInstance()));
					}

					return $value->getElement()->getId();

				case is_array($value) && isset($value['@id']) && $value['@id'] instanceof Serializer\ID:

					if ($value['@id']->getId() === null) {
						unset($value['@id']);
					} else {
						$value['@id'] = (string) $value['@id']->getId();
					}

				default: return $value;

			}

		});
	}

	private static function mapRecursive(array $array, callable $callback, bool $top = true): array
	{
		if ($top) {
			$array = $callback($array);
		}

		foreach ($array as $key => $value) {

			$array[$key] = $callback($value);

			if (is_array($array[$key])) {
				$array[$key] = self::mapRecursive($array[$key], $callback, false);
			}

		}

		return $array;
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

		$collectionReflection = $this->reflector->instance($value);

		if (!$collectionReflection instanceof Reflection\Collection) {
			throw new RuntimeException('Reflection is expected to be an instance of ' . Reflection\Collection::class . '.');
		}

		$root = [$collectionReflection->getMap() => []];

		foreach ($value as $item) {
			$root[$collectionReflection->getMap()][] = $this->serialize($item);
		}

		foreach ($collectionReflection->getProperties() as $collectionProperty) {

			if (!$collectionProperty instanceof MappedProperty) {
				throw new RuntimeException('Collection properties are expected to be mapped.');
			}

			if (!$collectionProperty->isNullable() || $collectionProperty->getValue() !== null) {
				$root[$collectionProperty->getMap()] = $this->serializeProperty($collectionProperty);
			}

		}

		return $root;
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