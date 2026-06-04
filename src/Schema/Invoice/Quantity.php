<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;

/**
 * Amount.
 */
class Quantity implements Entity
{

	use Backing;

	public ?string $content {
		get => $this->node->text;
		set { $this->node->text = $value; }
	}

	/** Unit. */
	public ?string $unitCode {
		get => $this->node->getString('@unitCode');
		set {
			$this->node->setString('@unitCode', $value);
		}
	}

}