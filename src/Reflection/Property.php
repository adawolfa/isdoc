<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Reflection;

use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\RuntimeException;
use Adawolfa\ISDOC\SimpleContentElement;
use DateTimeInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * Property reflection.
 *
 * @template T of object
 * @internal
 */
class Property
{

	/**
	 * @var Instance<T>
	 */
	private Instance $instance;

	private ReflectionProperty $property;

	private ?ReflectionNamedType $type;

	/**
	 * @param Instance<T> $instance
	 */
	public function __construct(Instance $instance, ReflectionProperty $property)
	{
		$this->instance = $instance;
		$this->property = $property;

		if ($property->hasType()) {

			if (!$property->getType() instanceof ReflectionNamedType) {
				throw new RuntimeException('Only named types are supported.');
			}

			$this->type = $property->getType();

		} else {
			$this->type = null;
		}
	}

	public function isPrimitive(): bool
	{
		return $this->type !== null
			   && $this->type->isBuiltin()
			   && $this->type->getName() !== 'array';
	}

	public function isDate(): bool
	{
		return $this->isA(DateTimeInterface::class);
	}

	public function isSimpleContentElement(): bool
	{
		return $this->isA(SimpleContentElement::class);
	}

	public function isCollection(): bool
	{
		return $this->isA(Collection::class);
	}

	private function isA(string $class): bool
	{
		return $this->type !== null
			   && !$this->type->isBuiltin()
			   && is_a($this->type->getName(), $class, true);
	}

	public function isNullable(): bool
	{
		return $this->type === null || $this->type->allowsNull();
	}

	public function getType(): ?ReflectionNamedType
	{
		return $this->type;
	}

	public function getExistingType(): ReflectionNamedType
	{
		return $this->getType() ?? throw new RuntimeException('Type was expected to be set.');
	}

	private function getMethod(string $name): ?callable
	{
		if (!$this->instance->getReflection()->hasMethod($name)) {
			return null;
		}

		try {
			$method = $this->instance->getReflection()->getMethod($name);
		} catch (ReflectionException $reflectionException) {
			throw new RuntimeException('Cannot create method reflection.', 0, $reflectionException);
		}

		if (!$method->isPublic() || $method->isStatic()) {
			return null;
		}

		return function () use ($name) {
			return $this->instance->getInstance()->$name(...func_get_args());
		};
	}

	private function getSetter(): ?callable
	{
		return $this->getMethod('set' . ucfirst($this->property->getName()));
	}

	private function getGetter(): ?callable
	{
		return $this->getMethod('get' . ucfirst($this->property->getName()));
	}

	public function accepts(string $type): bool
	{
		if ($this->type === null) {
			return true;
		}

		if ($this->type->isBuiltin()) {
			return $type === $this->type->getName();
		}

		return is_a($type, $this->type->getName(), true);
	}

	public function setValue(mixed $value): void
	{
		if ($value === null
			&& $this->type !== null
			&& !$this->type->allowsNull()) {
			throw new RuntimeException('Property is not nullable.');
		}

		$setter = $this->getSetter();

		if ($setter !== null) {
			$setter($value);
			return;
		}

		$this->property->setAccessible(true);
		$this->property->setValue($this->instance->getInstance(), $value);
		$this->property->setAccessible(false);
	}

	/** @return mixed */
	public function getValue()
	{
		$getter = $this->getGetter();

		if ($getter !== null) {
			$value = $getter();
		} else {
			$this->property->setAccessible(true);
			$value = $this->property->getValue($this->instance->getInstance());
			$this->property->setAccessible(false);
		}

		return $value;
	}

	public function getClass(): string
	{
		return $this->instance->getReflection()->name;
	}

	public function getName(): string
	{
		return $this->property->name;
	}

	/**
	 * @return Instance<T>
	 */
	public function getInstance(): Instance
	{
		return $this->instance;
	}

}