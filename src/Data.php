<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC\Data\Value;

/**
 * Wrapper for XML data traversal.
 */
final class Data
{

	/** @var array<string, mixed> */
	private array $data;

	private ?self $parent;

	private ?string $name;

	/**
	 * @param array<string, mixed> $data
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

		return array_map(fn(array $item): self => new self($item, $this, $name), $list);
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public static function create(array $data): self
	{
		return new self($data);
	}

	public static function createEmpty(self $parent, string $name): self
	{
		return new self([], $parent, $name);
	}

}