<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use IteratorAggregate;
use Countable;
use Nette\SmartObject;

/**
 * Generic collection.
 *
 * @template T
 * @template-implements IteratorAggregate<int, T>
 */
abstract class Collection implements IteratorAggregate, Countable, Arrayable
{

	use SmartObject;

	/** @var array<int, T> */
	protected array $items = [];

	public function count(): int
	{
		return count($this->items);
	}

	public function toArray(): array
	{
		return array_map(fn($value) => $value instanceof Arrayable ? $value->toArray() : $value, $this->items);
	}

}