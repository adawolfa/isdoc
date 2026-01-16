<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Reflection;

use Adawolfa\ISDOC\Collection as TCollection;
use Adawolfa\ISDOC\RuntimeException;
use ArrayAccess;
use ReflectionException;
use ReflectionNamedType;
use ReflectionObject;

/**
 * Collection reflection.
 *
 * @template T of TCollection
 * @extends Instance<T>
 * @internal
 */
final class Collection extends Instance
{

	private string $map;

	private string $type;

	private bool $unwrap;

	/**
	 * @param T&object                     $instance
	 * @param InstancePropertyFactory<T>[] $properties
	 */
	public function __construct(
		object           $instance,
		ReflectionObject $reflection,
		array            $properties,
		string           $map,
		string           $type,
		bool             $unwrap = false,
	)
	{
		parent::__construct($instance, $reflection, $properties);
		$this->map    = $map;
		$this->type   = $type;
		$this->unwrap = $unwrap;
	}

	public function getMap(): string
	{
		return $this->map;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getUnwrap(): bool
	{
		return $this->unwrap;
	}

	public function add(object $item): void
	{
		if (!is_a($item, $this->type)) {
			throw new RuntimeException(sprintf("Item must be an instance of %s, got %s.", $this->type, get_class($item)));
		}

		$method = null;

		try {
			$method = $this->getReflection()->getMethod('add');
		} catch (ReflectionException) {
			// NOP.
		}

		if ($method !== null && $method->isPublic() && !$method->isStatic() && $method->getNumberOfParameters() === 1) {

			$parameter = $method->getParameters()[0];

			if (!$parameter->hasType()
				|| $parameter->getType() instanceof ReflectionNamedType
				   && !$parameter->getType()->isBuiltin()
				   && is_a($item, $parameter->getType()->getName())) {
				$instance = $this->getInstance();
				$instance->add($item); // @phpstan-ignore-line
				return;
			}

		}

		try {
			$property = $this->getReflection()->getProperty('items');
		} catch (ReflectionException $exception) {
			throw new RuntimeException("Unable to create reflection of $this->type::\$items.", 0, $exception);
		}

		$property->setAccessible(true);

		$items = $property->getValue($this->getInstance());
		assert(is_array($items) || $items instanceof ArrayAccess);

		$items[] = $item;

		$property->setValue($this->getInstance(), $items);
		$property->setAccessible(false);
	}

}