<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;

/**
 * Note.
 */
class Note implements Entity
{

	use Backing;

	public ?string $content {
		get => $this->node->text;
		set { $this->node->text = $value; }
	}

	/** Language identifier (e.g. "en" for English). */
	public ?string $languageID {
		get => $this->node->getString('@languageID');
		set {
			Restriction::pattern($value, '[a-zA-Z]{1,8}(-[a-zA-Z0-9]{1,8})*');
			$this->node->setString('@languageID', $value);
		}
	}

}