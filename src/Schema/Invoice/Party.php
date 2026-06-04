<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;

/**
 * Information about an organization, sub-organization, or individual fulfilling a role in a business process.
 */
class Party implements Entity
{

	use Backing;

	/** Information about a party's identification. */
	public PartyIdentification $partyIdentification {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('PartyIdentification', PartyIdentification::class);
		set { $this->node->setChild('PartyIdentification', $value); }
	}

	/** Information about a party's name. */
	public PartyName $partyName {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('PartyName', PartyName::class);
		set { $this->node->setChild('PartyName', $value); }
	}

	/** Postal address. */
	public PostalAddress $postalAddress {
		/** @throws Exception */
		get => $this->node->getChildOrThrow('PostalAddress', PostalAddress::class);
		set { $this->node->setChild('PostalAddress', $value); }
	}

	/** Information about a party's tax scheme. */
	public ?PartyTaxSchemes $partyTaxSchemes {
		get => count($this->node->getChildren('PartyTaxScheme')) > 0 ? $this->node->view(PartyTaxSchemes::class) : null;
		set {
			$this->node->remove('PartyTaxScheme');
			foreach ($value ?? [] as $item) {
				$this->node->addChild('PartyTaxScheme', $item);
			}
		}
	}

	/** Commercial Register record identification (in the Czech Republic). */
	public ?RegisterIdentification $registerIdentification {
		get => $this->node->getChild('RegisterIdentification', RegisterIdentification::class);
		set { $this->node->setChild('RegisterIdentification', $value); }
	}

	/** Information about a contactable person or organization department. */
	public ?Contact $contact {
		get => $this->node->getChild('Contact', Contact::class);
		set { $this->node->setChild('Contact', $value); }
	}

	public function __construct(
		PartyIdentification $partyIdentification,
		PartyName $partyName,
		PostalAddress $postalAddress,
	)
	{
		$this->partyIdentification = $partyIdentification;
		$this->partyName = $partyName;
		$this->postalAddress = $postalAddress;
	}

}