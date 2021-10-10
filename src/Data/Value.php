<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Data;
use Adawolfa\ISDOC\Data;
use Adawolfa\ISDOC\RuntimeException;
use ReflectionNamedType;
use DateTimeImmutable;
use Error;

final class Value
{

	private const DATE_FORMAT = 'Y-m-d';

	/** @var mixed */
	private        $value;
	private Data   $parent;
	private string $name;

	/** @param mixed $value */
	public function __construct($value, Data $parent, string $name)
	{
		$this->value  = $value;
		$this->parent = $parent;
		$this->name   = $name;
	}

	public function getPath(): string
	{
		return $this->parent->getPath() . ($this->parent->getPath() === '' ? '' : '/') . $this->name;
	}

	/**
	 * @return mixed
	 * @throws ValueException
	 */
	public function cast(?ReflectionNamedType $type)
	{
		if ($type !== null && !$type->isBuiltin()) {
			throw new RuntimeException(__METHOD__ . '() can only cast to a primitive type, ' . $type->getName() . ' given.');
		}

		if ($this->value === null) {

			if ($type !== null && !$type->allowsNull()) {
				throw ValueException::missing($this);
			}

			return null;

		}

		return $this->as($type->getName());
	}

	/** @throws ValueException */
	public function toString(): ?string
	{
		if ($this->value === null) {
			return null;
		}

		return $this->as('string');
	}

	/** @throws ValueException */
	public function toDate(): ?DateTimeImmutable
	{
		if ($this->value === null || $this->value === '') {
			return null;
		}

		$parsed = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $this->value);

		if ($parsed === false) {
			throw ValueException::invalidDateFormat($this);
		}

		return $parsed->setTime(0, 0);
	}

	/**
	 * @return mixed
	 * @throws ValueException
	 */
	private function as(string $type)
	{
		$value = $this->value;

		try {

			if (settype($value, $type)) {
				return $value;
			}

		} catch (Error $error) {
			// Elevated later on.
		}

		throw ValueException::cannotCast($this, $value, $type);
	}

}