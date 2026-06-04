<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;

/**
 * Contract for indefinite period.
 */
class LastValidDateUnbounded implements Entity
{

	use Backing;
}