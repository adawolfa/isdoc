<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about an organization, sub-organization, or individual fulfilling a role in a business process.
 *
 * @property PartyIdentification         $partyIdentification
 * @property PartyName                   $partyName
 * @property PostalAddress               $postalAddress
 * @property PartyTaxScheme|null         $partyTaxScheme
 * @property RegisterIdentification|null $registerIdentification
 * @property Contact|null                $contact
 */
class Party implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Information about a party's identification. */
	#[Map('PartyIdentification')]
	private PartyIdentification $partyIdentification;

	/** Information about a party's name. */
	#[Map('PartyName')]
	private PartyName $partyName;

	/** Postal address. */
	#[Map('PostalAddress')]
	private PostalAddress $postalAddress;

	/** Information about a party's tax scheme. */
	#[Map('PartyTaxScheme')]
	private ?PartyTaxScheme $partyTaxScheme = null;

	/** Commercial Register record identification (in the Czech Republic). */
	#[Map('RegisterIdentification')]
	private ?RegisterIdentification $registerIdentification = null;

	/** Information about a contactable person or organization department. */
	#[Map('Contact')]
	private ?Contact $contact = null;

	public function __construct(
		PartyIdentification $partyIdentification,
		PartyName $partyName,
		PostalAddress $postalAddress
	) {
		$this->setPartyIdentification($partyIdentification);
		$this->setPartyName($partyName);
		$this->setPostalAddress($postalAddress);
	}

	public function getPartyIdentification(): PartyIdentification
	{
		return $this->partyIdentification;
	}

	public function setPartyIdentification(PartyIdentification $partyIdentification): self
	{
		$this->partyIdentification = $partyIdentification;
		return $this;
	}

	public function getPartyName(): PartyName
	{
		return $this->partyName;
	}

	public function setPartyName(PartyName $partyName): self
	{
		$this->partyName = $partyName;
		return $this;
	}

	public function getPostalAddress(): PostalAddress
	{
		return $this->postalAddress;
	}

	public function setPostalAddress(PostalAddress $postalAddress): self
	{
		$this->postalAddress = $postalAddress;
		return $this;
	}

	public function getPartyTaxScheme(): ?PartyTaxScheme
	{
		return $this->partyTaxScheme;
	}

	public function setPartyTaxScheme(?PartyTaxScheme $partyTaxScheme): self
	{
		$this->partyTaxScheme = $partyTaxScheme;
		return $this;
	}

	public function getRegisterIdentification(): ?RegisterIdentification
	{
		return $this->registerIdentification;
	}

	public function setRegisterIdentification(?RegisterIdentification $registerIdentification): self
	{
		$this->registerIdentification = $registerIdentification;
		return $this;
	}

	public function getContact(): ?Contact
	{
		return $this->contact;
	}

	public function setContact(?Contact $contact): self
	{
		$this->contact = $contact;
		return $this;
	}

}