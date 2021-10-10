<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Reflection;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\RuntimeException;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use Adawolfa\ISDOC;
use ReflectionProperty;

final class Reflector
{

	private AnnotationReader $annotationReader;

	public function __construct(AnnotationReader $annotationReader)
	{
		$this->annotationReader = $annotationReader;
	}

	/**
	 * @template T
	 * @param class-string<T> $class
	 * @return Instance<T>
	 */
	public function class(string $class): Instance
	{
		try {
			$reflection = new ReflectionClass($class);
			$instance   = $reflection->newInstanceWithoutConstructor();
		} catch (ReflectionException $reflectionException) {
			throw new RuntimeException('Failed to instantiate the class.', 0, $reflectionException);
		}

		return $this->instance($instance);
	}

	/**
	 * @template T
	 * @param T $instance
	 * @return Instance<T>
	 */
	public function instance(object $instance): Instance
	{
		$object     = new ReflectionObject($instance);
		$properties = [];

		foreach ($this->getProperties($object) as $reflectionProperty) {

			$map = $this->annotationReader->getPropertyAnnotation($reflectionProperty, Map::class);

			if ($map instanceof Map) {
				$properties[] = new InstanceMappedPropertyFactory($reflectionProperty, $map->getValue());
			}

			$reference = $this->annotationReader->getPropertyAnnotation($reflectionProperty, ISDOC\Reference::class);

			if ($reference instanceof ISDOC\Reference) {
				$properties[] = new InstanceReferencePropertyFactory($reflectionProperty);
			}

		}

		if ($instance instanceof ISDOC\Collection) {

			$map = $this->annotationReader->getClassAnnotation($object, Map::class);

			if ($map === null) {
				throw new RuntimeException('Collection is required to have @' . Map::class . ' annotation.');
			}

			$itemClassName = $this->parseCollectionItemClassName($object);

			if ($itemClassName === null) {
				throw new RuntimeException('Collection is required to have @extends annotation of ' . ISDOC\Collection::class . '.');
			}

			return new Collection($instance, $object, $properties, $map->getValue(), $itemClassName);

		}

		return new Instance($instance, $object, $properties);
	}

	private function parseCollectionItemClassName(ReflectionClass $class): ?string
	{
		$doc = $class->getDocComment();

		if (!preg_match_all('~\*\s+@extends\s+(?<generic>[^<]+)<(?<T>[A-Za-z0-9_\\\]+)>~', $doc, $matches)) {
			return null;
		}

		// TODO: Make this resolve names and stuff.
		foreach ($matches['generic'] as $i => $generic) {

			$itemClassName = $class->getNamespaceName() . '\\' . $matches['T'][$i];

			if ($generic === 'Collection' && class_exists($itemClassName)) {
				return $itemClassName;
			}

		}

		return null;
	}

	/** @return ReflectionProperty[] */
	private function getProperties(ReflectionClass $reflection): array
	{
		$properties = $reflection->getParentClass() === false ? [] : $this->getProperties($reflection->getParentClass());

		foreach ($reflection->getProperties(~ReflectionProperty::IS_STATIC) as $property) {
			$properties[$property->getName()] = $property;
		}

		return $properties;
	}

}