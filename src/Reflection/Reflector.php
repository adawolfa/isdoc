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

/**
 * Schema classes introspections.
 *
 * @internal
 */
final class Reflector
{

	private AnnotationReader $annotationReader;
	private array            $collectionItemClassNamesCache = [];

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
		if (isset($this->collectionItemClassNamesCache[$class->name])) {
			return $this->collectionItemClassNamesCache[$class->name];
		}

		$uses = $this->parseUses($class);
		$doc  = $class->getDocComment();

		if (!preg_match_all('~\*\s+@extends\s+(?<generic>[^<]+)<(?<T>[A-Za-z0-9_\\\]+)>~', $doc, $matches)) {
			return null;
		}

		foreach ($matches['generic'] as $i => $generic) {

			$generic       = $this->resolveClass($uses, $class, $generic);
			$itemClassName = $this->resolveClass($uses, $class, $matches['T'][$i]);

			if ($generic !== null && is_a($generic, ISDOC\Collection::class, true) && $itemClassName !== null) {
				return $itemClassName;
			}

		}

		return null;
	}

	private function resolveClass(array $uses, ReflectionClass $class, string $alias): ?string
	{
		if (strpos($alias, '\\') === 0) {
			$alias = substr($alias, 1);
			return class_exists($alias) ? $alias : null;
		}

		$fqn = ltrim($class->getNamespaceName() . '\\' . $alias, '\\');

		if (class_exists($fqn)) {
			return $fqn;
		}

		$first = explode('\\', $alias)[0];
		$alias = strpos($alias, '\\') === false ? '' : substr($alias, strpos($alias, '\\') + 1);

		if (isset($uses[$first]) && class_exists(rtrim($uses[$first] . '\\' . $alias, '\\'))) {
			return rtrim($uses[$first] . '\\' . $alias, '\\');
		}

		return null;
	}

	private function parseUses(ReflectionClass $class): array
	{
		$code = @file_get_contents($class->getFileName());

		if ($code === false) {
			throw new RuntimeException("Failed to read contents of '{$class->getFileName()}'.");
		}

		$tokens = token_get_all($code);
		$uses   = [];

		for ($i = 0; isset($tokens[$i]); $i++) {

			if (is_array($tokens[$i]) && $tokens[$i][0] === T_USE) {

				$use = '';
				$alias = null;

				for (; isset($tokens[$i]); $i++) {

					switch (true) {

						case is_array($tokens[$i]) && $tokens[$i][0] === T_STRING:
							$use .= $alias = $tokens[$i][1];
							break;

						case is_array($tokens[$i]) && $tokens[$i][0] === T_NS_SEPARATOR:
							$use .= '\\';
							break;

						case $tokens[$i] === ';'
							|| is_array($tokens[$i]) && $tokens[$i][0] === T_AS:
							break 2;

					}

				}

				if (is_array($tokens[$i]) && $tokens[$i][0] === T_AS) {

					for ($i++; isset($tokens[$i]) && $tokens[$i] !== ';'; $i++) {

						if (is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
							$alias = $tokens[$i][1];
						}

					}

				}

				$uses[$alias] = $use;

			}

		}

		return $uses;
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