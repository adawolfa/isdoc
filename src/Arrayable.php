<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

/**
 * @deprecated 2.0 will no longer provide {@code toArray()}
 */
interface Arrayable
{

	/**
	 * @return array<string|int, mixed>
	 */
	public function toArray(): array;

}