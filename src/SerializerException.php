<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Adawolfa\ISDOC\Reflection\Property;

final class SerializerException extends Exception
{

	public static function propertyNotNullable(Property $property): self
	{
		return new self("Property {$property->getClass()}::\${$property->getName()} is not nullable.");
	}

	public static function referencedElementNotFound(string $class): self
	{
		return new self("Referenced element of type $class not found.");
	}

}