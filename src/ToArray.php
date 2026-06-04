<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use ReflectionClass;
use ReflectionProperty;

/**
 * Automatic object to array conversion.
 */
trait ToArray
{

	/**
	 * @return array<string, mixed>
	 * @deprecated 2.0 will no longer provide {@code toArray()}
	 */
	public function toArray(): array
	{
		$reflection = new ReflectionClass($this);
		$data       = [];
		$properties = [];

		do {

			foreach ($reflection->getProperties(~ReflectionProperty::IS_STATIC) as $property) {

				if (isset($properties[$property->getName()])) {
					continue;
				}

				$properties[$property->getName()] = $property;

			}

			$reflection = $reflection->getParentClass();

		} while ($reflection !== false);

		foreach ($properties as $property) {

			$value = $this->{$property->getName()};

			if ($value instanceof Arrayable) {
				$value = $value->toArray();
			}

			$data[$property->getName()] = $value;

		}

		return $data;
	}

}