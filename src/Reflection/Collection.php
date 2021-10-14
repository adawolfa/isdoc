<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Reflection;
use Adawolfa\ISDOC\RuntimeException;
use ReflectionObject;
use ReflectionException;

/**
 * Collection reflection.
 *
 * @template T
 * @internal
 */
final class Collection extends Instance
{

	private string $map;
	private string $itemClassName;

	/**
	 * @param T                         $instance
	 * @param InstancePropertyFactory[] $properties
	 */
	public function __construct(
		object           $instance,
		ReflectionObject $reflection,
		array            $properties,
		string           $map,
		string           $itemClassName
	)
	{
		parent::__construct($instance, $reflection, $properties);
		$this->map           = $map;
		$this->itemClassName = $itemClassName;
	}

	public function getMap(): ?string
	{
		return $this->map;
	}

	public function getItemClassName(): string
	{
		return $this->itemClassName;
	}

	public function add(object $item): void
	{
		if (!is_a($item, $this->itemClassName)) {
			throw new RuntimeException(sprintf("Item must be an instance of %s, got %s.", $this->itemClassName, get_class($item)));
		}

		$method = null;

		try {
			$method = $this->getReflection()->getMethod('add');
		} catch (ReflectionException $exception) {
			// NOP.
		}

		if ($method !== null && $method->isPublic() && !$method->isStatic() && $method->getNumberOfParameters() === 1) {

			$parameter = $method->getParameters()[0];

			if (!$parameter->hasType() || !$parameter->getType()->isBuiltin() && is_a($item, $parameter->getType()->getName())) {
				$instance = $this->getInstance();
				$instance->add($item);
				return;
			}

		}

		try {
			$property = $this->getReflection()->getProperty('items');
		} catch (ReflectionException $exception) {
			throw new RuntimeException("Unable to create reflection of {$this->itemClassName}::\$items.");
		}

		$property->setAccessible(true);

		$items = $property->getValue($this->getInstance());
		$items[] = $item;

		$property->setValue($this->getInstance(), $items);
		$property->setAccessible(false);
	}

}