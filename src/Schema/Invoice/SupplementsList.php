<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Collection of document attachments. Exactly one attachment can be document preview marked by preview="true".
 *
 * @Map("Supplement")
 * @extends Collection<Supplement>
 */
class SupplementsList extends Collection
{

	/** @return ArrayIterator|Supplement[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(Supplement $supplement): self
	{
		$this->items[] = $supplement;
		return $this;
	}

}