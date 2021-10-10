<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

trait ToArray
{

	public function toArray(): array
	{
		try {
			$reflection = new ReflectionClass($this);
		} catch (ReflectionException $exception) {
			throw new RuntimeException('Failed to create reflection.', 0, $exception);
		}

		$data = [];

		foreach ($reflection->getProperties(~ReflectionProperty::IS_STATIC) as $property) {

			$value = $this->{$property->getName()};

			if ($value instanceof Arrayable) {
				$value = $value->toArray();
			}

			$data[$property->getName()] = $value;

		}

		return $data;
	}

}