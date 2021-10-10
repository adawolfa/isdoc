<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about a bank account.
 */
class AlternateBankAccount implements Arrayable
{

	use SmartObject;
	use ToArray;

}