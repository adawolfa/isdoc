<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Reflection;

/**
 * @template T of object
 * @extends InstancePropertyFactory<T>
 * @internal
 */
final class InstanceReferencePropertyFactory extends InstancePropertyFactory
{

	/**
	 * @param Instance<T> $instance
	 * @return Property<object>
	 */
	public function create(Instance $instance): Property
	{
		return new ReferenceProperty($instance, $this->property);
	}

}