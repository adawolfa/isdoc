<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\SimpleContentElement;
use Adawolfa\ISDOC\ToArray;

/**
 * Note.
 *
 * @property string|null $languageID
 */
class Note extends SimpleContentElement implements Arrayable
{

	use ToArray;

	/**
	 * Language identifier (e.g. "en" for English).
	 *
	 * @Map("@languageID")
	 */
	private ?string $languageID = null;

	public function getLanguageID(): ?string
	{
		return $this->languageID;
	}

	public function setLanguageID(?string $languageID): self
	{
		Restriction::pattern($languageID, '[a-zA-Z]{1,8}(-[a-zA-Z0-9]{1,8})*');
		$this->languageID = $languageID;
		return $this;
	}

}