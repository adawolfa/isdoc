<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Reflection;
use ReflectionObject;

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
	 * @param InstancePropertyFactory[] $mappedProperties
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

}