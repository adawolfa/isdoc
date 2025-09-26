<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC\Data\MissingValueException;
use Adawolfa\ISDOC\Data\Value;
use Adawolfa\ISDOC\Data\ValueException;
use Adawolfa\ISDOC\Reflection\Instance;
use Adawolfa\ISDOC\Reflection\MappedProperty;
use Adawolfa\ISDOC\Reflection\Property;
use Adawolfa\ISDOC\Reflection\ReferenceProperty;
use Adawolfa\ISDOC\Reflection\Reflector;
use Adawolfa\ISDOC\Schema\Invoice\Party;
use Adawolfa\ISDOC\Schema\Invoice\PartyTaxScheme;

/**
 * Instantiates classes and hydrates them with data from XML decoder.
 *
 * @internal
 */
final class Hydrator
{

	private Reflector $reflector;

	/** @var callable[] */
	private array $finishers = [];

	private int $depth = 0;

	/** @var callable|null */
	private $hook;

	/** @var array<string, array<string, Instance<object>>> */
	private array $references = [];

	private bool $skipMissingPrimitiveValues;

	private ?string $preferredTaxScheme = null;

	public function __construct(
		Reflector $reflector,
		bool      $skipMissingPrimitiveValues = false,
		?string   $preferredTaxScheme = null,
	)
	{
		$this->reflector                  = $reflector;
		$this->skipMissingPrimitiveValues = $skipMissingPrimitiveValues;
		$this->preferredTaxScheme         = $preferredTaxScheme;
	}

	/**
	 * @template T of object
	 * @param Data            $data
	 * @param class-string<T> $class
	 * @param callable|null   $hook
	 * @return T&object
	 */
	public function hydrate(Data $data, string $class, ?callable $hook = null): object
	{
		if ($this->depth > 0 && $hook !== null) {
			throw new RuntimeException('Hook cannot be attached at this point.');
		}

		if ($hook !== null) {
			$this->hook = $hook;
		}

		$this->depth++;

		try {

			$instance = $this->reflector->class($class);

			if ($data->hasValue('@id')) {
				$this->registerReference($data->getValue('@id'), $instance);
			}

			foreach ($instance->getProperties() as $property) {
				$this->hydrateProperty($data, $property);
			}

			$hydrated = $instance->getInstance();

			if ($this->hook !== null) {
				$hydrated = call_user_func($this->hook, $hydrated);
			}

			return $hydrated;

		} finally {

			if (--$this->depth === 0) {
				$this->hook = null;
				$this->finish();
			}

		}
	}

	private function finish(): void
	{
		try {

			foreach ($this->finishers as $finisher) {
				$finisher();
			}

		} finally {
			$this->references = $this->finishers = [];
		}
	}

	/**
	 * @template T of object
	 * @param Instance<T> $instance
	 * @throws Data\Exception
	 */
	private function registerReference(Value $value, Instance $instance): void
	{
		$id = $value->toString();
		$namespace = $value->getParent()->getName();

		if ($id === null) {
			throw Data\Exception::missingReferenceId($value->getPath());
		}

		if ($namespace === null) {
			throw new RuntimeException('Cannot register reference without namespace.');
		}

		if (isset($this->references[$namespace][$id])) {
			throw Data\Exception::duplicateReferenceId($id);
		}

		if (!isset($this->references[$namespace])) {
			$this->references[$namespace] = [];
		}

		$this->references[$namespace][$id] = $instance;
	}

	/**
	 * @template T of object
	 * @param Property<T> $property
	 * @throws Data\Exception
	 */
	private function hydrateProperty(Data $data, Property $property): void
	{
		switch (true) {

			case $property instanceof MappedProperty:
				$this->hydrateMappedProperty($data, $property);
				break;

			case $property instanceof ReferenceProperty:
				$this->hydrateReferenceProperty($data, $property);
				break;

		}
	}

	/**
	 * @template T of object
	 * @param ReferenceProperty<T> $property
	 * @throws Data\Exception
	 */
	private function hydrateReferenceProperty(Data $data, ReferenceProperty $property): void
	{
		if (!$data->hasValue('@ref')) {

			if ($property->getType() !== null) {
				/** @var class-string $type */
				$type = $property->getExistingType()->getName();
				$property->setValue($this->hydrate($data, $type));
				return;
			}

			throw Data\Exception::missingReferenceId($data->getPath());

		}

		$this->finishers[] = function () use ($data, $property): void {

			$id = $data->getValue('@ref');

			if (!isset($this->references[$data->getName()][$id->toString()])) {
				throw Data\Exception::referencedElementNotFound($id->toString() ?? '', $id->getPath());
			}

			if (!$property->accepts($this->references[$data->getName()][$id->toString()]->getReflection()->name)) {
				throw Data\Exception::referencedElementTypeMismatch(
					$property->getExistingType()->getName(),
					$this->references[$data->getName()][$id->toString()]->getReflection()->getName(),
				);
			}

			$property->setValue($this->references[$data->getName()][$id->toString()]->getInstance());

		};
	}

	/**
	 * @template T of object
	 * @param MappedProperty<T> $property
	 * @throws Data\Exception
	 */
	private function hydrateMappedProperty(Data $data, MappedProperty $property): void
	{
		switch (true) {

			case $property->isPrimitive():
				$this->hydratePrimitiveProperty($data, $property);
				break;

			case $property->isDate():
				$this->hydrateDateProperty($data, $property);
				break;

			case $property->isSimpleContentElement():
				$this->hydrateSimpleContentElementProperty($data, $property);
				break;

			default:
				$this->hydrateComplexProperty($data, $property);
				break;

		}
	}

	/**
	 * @template T of object
	 * @param MappedProperty<T> $property
	 * @throws ValueException
	 */
	private function hydratePrimitiveProperty(Data $data, MappedProperty $property): void
	{
		// FIXME: Support elements with unbound max occurrences.
		if (($property->getInstance()->getReflection()->getName() === PartyTaxScheme::class
			 || $property->getInstance()->getReflection()->isSubclassOf(PartyTaxScheme::class))
			&& $data->isList()) {

			$found = false;

			if ($this->preferredTaxScheme !== null) {

				foreach ($data->getListElements() as $child) {

					if (!$child->hasValue('TaxScheme')) {
						continue;
					}

					$taxScheme = $child->getValue('TaxScheme')->toString();

					if ($taxScheme === null || strcasecmp($taxScheme, $this->preferredTaxScheme) !== 0) {
						continue;
					}

					$data  = $child;
					$found = true;
					break;

				}

			}

			if (!$found) {
				$data = $data->getFirstListElement();
			}

			if ($data === null) {
				throw new RuntimeException('List element was expected.');
			}

		}

		try {
			$property->setValue($data->getValue($property->getMap())->cast($property->getType()));
		} catch (MissingValueException $exception) {

			if (!$this->skipMissingPrimitiveValues) {
				throw $exception;
			}

		}
	}

	/**
	 * @template T of object
	 * @param MappedProperty<T> $property
	 * @throws Data\Exception
	 */
	private function hydrateDateProperty(Data $data, MappedProperty $property): void
	{
		$value = $data->getValue($property->getMap());
		$date  = $value->toDate();

		if ($date === null && !$property->isNullable()) {
			throw Data\Exception::missingRequiredChild($property->getMap(), $value->getPath());
		}

		$property->setValue($date);
	}

	/**
	 * @template T of object
	 * @param MappedProperty<T> $property
	 * @throws Data\Exception
	 */
	private function hydrateSimpleContentElementProperty(Data $data, MappedProperty $property): void
	{
		if (!$data->hasChild($property->getMap()) && !$data->hasValue($property->getMap())) {

			if ($property->isNullable()) {
				$property->setValue(null);
				return;
			}

			throw Data\Exception::missingRequiredChild($property->getMap(), $data->getPath());

		}

		if ($data->hasChild($property->getMap())) {
			$child = $data->getChild($property->getMap());
		} else {
			$child = Data::createEmpty($data, $property->getMap());
		}

		/** @var class-string $type */
		$type  = $property->getExistingType()->getName();
		$value = $this->hydrate($child, $type);

		if (!$value instanceof SimpleContentElement) {
			throw new RuntimeException('Value was expected to be an instance of ' . SimpleContentElement::class . '.');
		}

		if ($data->hasChild($property->getMap())) {
			$value->setContent($child->getValue('#')->toString());
		} else {
			$value->setContent($data->getValue($property->getMap())->toString());
		}

		$property->setValue($value);
	}

	/**
	 * @template T of object
	 * @param MappedProperty<T> $property
	 * @throws Data\Exception
	 */
	private function hydrateComplexProperty(Data $data, MappedProperty $property): void
	{
		if (!$data->hasChild($property->getMap())) {

			// This is hack around empty collections.
			if ($property->isCollection()
				&& $data->hasValue($property->getMap())
				&& $data->getValue($property->getMap())->toString() === '') {

				/** @var class-string $type */
				$type  = $property->getExistingType()->getName();
				$value = $this->hydrate(Data::createEmpty($data, $property->getMap()), $type);

				$property->setValue($value);
				return;

			}

			if (!$property->isNullable()) {
				throw Data\Exception::missingRequiredChild($property->getMap(), $data->getPath());
			}

			$property->setValue(null);
			return;

		}

		$child = $data->getChild($property->getMap());

		/** @var class-string $type */
		$type  = $property->getExistingType()->getName();
		$value = $this->hydrate($child, $type);

		if ($value instanceof Collection) {

			$collection = $this->reflector->instance($value);

			if (!$collection instanceof Reflection\Collection) {
				throw new RuntimeException('Collection reflection was expected to be instance of ' . Reflection\Collection::class . '.');
			}

			foreach ($child->getChildList($collection->getMap()) as $itemData) {
				/** @var class-string $type */
				$type = $collection->getType();
				$collection->add($this->hydrate($itemData, $type));
			}

		}

		$property->setValue($value);
	}

}