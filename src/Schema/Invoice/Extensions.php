<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Arbitrary fragment of user-defined elements. Elements must use their own namespace.
 */
class Extensions implements Arrayable
{

	use SmartObject;
	use ToArray;

}