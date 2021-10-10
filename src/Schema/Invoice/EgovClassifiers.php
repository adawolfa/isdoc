<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Collection;
use Adawolfa\ISDOC\Map;
use ArrayIterator;

/**
 * Collection of classifiers.
 *
 * @Map("EgovClassifier")
 * @extends Collection<string>
 */
class EgovClassifiers extends Collection
{

	/** @return ArrayIterator|string[] */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function add(string $egovClassifier): self
	{
		$this->items[] = $egovClassifier;
		return $this;
	}

}