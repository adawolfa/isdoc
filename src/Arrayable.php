<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

interface Arrayable
{

	/**
	 * @return array<string|int, mixed>
	 */
	public function toArray(): array;

}