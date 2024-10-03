<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Countable;
use IteratorAggregate;

/**
 * Generic collection.
 *
 * @template T
 * @template-implements IteratorAggregate<int, T>
 */
abstract class Collection implements IteratorAggregate, Countable, Arrayable
{

	/** @var array<int, T> */
	protected array $items = [];

	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * @return array<int, array<string|int, mixed>|T>
	 */
	public function toArray(): array
	{
		return array_map(fn($value) => $value instanceof Arrayable ? $value->toArray() : $value, $this->items);
	}

}