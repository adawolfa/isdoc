<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Reflection;

/** @internal */
final class InstanceReferencePropertyFactory extends InstancePropertyFactory
{

	public function create(Instance $instance): Property
	{
		return new ReferenceProperty($instance, $this->property);
	}

}