<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Anonymous receiver of simplified tax document.
 *
 * @property string      $id
 * @property string|null $idScheme
 */
class AnonymousCustomerParty implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Unique identifier. */
	#[Map('ID')]
	private string $id;

	/** Identification of schema used for identifier construction. */
	#[Map('IDScheme')]
	private ?string $idScheme = null;

	public function __construct(string $id)
	{
		$this->setId($id);
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function getId(): string
	{
		return $this->id;
	}

	/** @deprecated Method accessors are deprecated, use {@see $id} property instead. */
	public function setId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $idScheme} property instead. */
	public function getIdScheme(): ?string
	{
		return $this->idScheme;
	}

	/** @deprecated Method accessors are deprecated, use {@see $idScheme} property instead. */
	public function setIdScheme(?string $idScheme): self
	{
		$this->idScheme = $idScheme;
		return $this;
	}

}