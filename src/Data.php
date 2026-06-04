<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC\Data\Value;

/**
 * Wrapper for XML data traversal.
 */
final class Data
{

	/** @var array<string|int, mixed> */
	private array $data;

	private ?self $parent;

	private ?string $name;

	/**
	 * @param array<string|int, mixed> $data
	 */
	private function __construct(array $data, ?self $parent = null, ?string $name = null)
	{
		$this->data   = $data;
		$this->parent = $parent;
		$this->name   = $name;
	}

	public function getPath(): string
	{
		return ($this->parent->name ?? '') . ($this->parent === null || $this->parent->getPath() === '' ? '' : '/') . $this->name;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function hasValue(string $name): bool
	{
		return array_key_exists($name, $this->data) && !is_array($this->data[$name]);
	}

	public function getValue(string $name): Value
	{
		return new Data\Value($this->data[$name] ?? null, $this, $name);
	}

	public function hasChild(string $name): bool
	{
		return isset($this->data[$name]) && is_array($this->data[$name]);
	}

	public function getChild(string $name): self
	{
		if (!$this->hasChild($name)) {
			throw new RuntimeException('Data does not contain such child.');
		}

		if (!is_array($this->data[$name])) {
			throw new RuntimeException('Child is not an array.');
		}

		return new self($this->data[$name], $this, $name);
	}

	/** @return self[] */
	public function getChildList(string $name): array
	{
		if (!$this->hasChild($name)) {
			return [];
		}

		$list = $this->data[$name];

		if (!is_array($list) || count($list) === 0) {
			return [];
		}

		if (!isset($list[0])) {
			$list = [$list];
		}

		$children = [];

		foreach ($list as $item) {

			if (!is_array($item)) {
				throw new RuntimeException('Child is not an array.');
			}

			$children[] = new self($item, $this, $name);

		}

		return $children;
	}

	/**
	 * @param array<string|int, mixed> $data
	 */
	public static function create(array $data): self
	{
		return new self($data);
	}

	public static function createEmpty(self $parent, string $name): self
	{
		return new self([], $parent, $name);
	}

	public function isList(): bool
	{
		return isset($this->data[0]);
	}

	public function isEmpty(): bool
	{
		return count($this->data) === 0;
	}

	public function getFirstListElement(): self
	{
		if (!$this->isList() || $this->isEmpty()) {
			throw new RuntimeException('Data is not a list or is empty.');
		}

		if (!is_array($this->data[0])) {
			throw new RuntimeException('First element is not an array.');
		}

		return new self($this->data[0], $this, $this->name);
	}

	/** @return self[] */
	public function getListElements(): array
	{
		if (!$this->isList() || $this->isEmpty()) {
			throw new RuntimeException('Data is not a list or is empty.');
		}

		$list = [];

		foreach ($this->data as $item) {

			if (!is_array($item)) {
				throw new RuntimeException('List item is not an array.');
			}

			$list[] = new self($item, $this, $this->name);

		}

		return $list;
	}

}