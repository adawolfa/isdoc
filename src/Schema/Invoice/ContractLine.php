<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Reference to a related contract.
 */
class ContractLine implements Entity
{

	use Backing;

	private ?Contract $isdocReferenceContract = null;

	public Contract $contract {
		/** @throws Exception */
		get {
			if ($this->isdocReferenceContract !== null) {
				return $this->isdocReferenceContract;
			}

			$ref = $this->node->getString('@ref');

			return $ref !== null
				? $this->node->getReference(Contract::class, $ref)
				: $this->node->view(Contract::class);
		}
		set {
			$this->isdocReferenceContract = $value;
			$this->node->setReference($value);
		}
	}

	/** Identifier of paragraph in an agreement. */
	public ?string $paragraphID {
		get => $this->node->getString('ParagraphID');
		set {
			$this->node->setString('ParagraphID', $value);
		}
	}

	public function __construct(
		Contract $contract,
	)
	{
		$this->contract = $contract;
	}

}