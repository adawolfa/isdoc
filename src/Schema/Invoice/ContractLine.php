<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Reference;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Reference to a related contract.
 *
 * @property Contract    $contract
 * @property string|null $paragraphID
 */
class ContractLine implements Arrayable
{

	use SmartObject;
	use ToArray;

	#[Reference]
	private Contract $contract;

	/** Identifier of paragraph in an agreement. */
	#[Map('ParagraphID')]
	private ?string $paragraphID = null;

	public function __construct(Contract $contract)
	{
		$this->setContract($contract);
	}

	/** @deprecated Method accessors are deprecated, use {@see $contract} property instead. */
	public function getContract(): Contract
	{
		return $this->contract;
	}

	/** @deprecated Method accessors are deprecated, use {@see $contract} property instead. */
	public function setContract(Contract $contract): self
	{
		$this->contract = $contract;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paragraphID} property instead. */
	public function getParagraphID(): ?string
	{
		return $this->paragraphID;
	}

	/** @deprecated Method accessors are deprecated, use {@see $paragraphID} property instead. */
	public function setParagraphID(?string $paragraphID): self
	{
		$this->paragraphID = $paragraphID;
		return $this;
	}

}