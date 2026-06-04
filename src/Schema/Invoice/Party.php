<?php declare(strict_types=1);

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
	use ToArray {
		ToArray::toArray as private traitToArray;
	}

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
	private ?PartyTaxSchemes $partyTaxSchemes = null;

	/** Commercial Register record identification (in the Czech Republic). */
	#[Map('RegisterIdentification')]
	private ?RegisterIdentification $registerIdentification = null;

	/** Information about a contactable person or organization department. */
	#[Map('Contact')]
	private ?Contact $contact = null;

	public function __construct(
		PartyIdentification $partyIdentification,
		PartyName $partyName,
		PostalAddress $postalAddress,
	) {
		$this->setPartyIdentification($partyIdentification);
		$this->setPartyName($partyName);
		$this->setPostalAddress($postalAddress);
	}

	/** @deprecated Method accessors are deprecated, use {@see $partyIdentification} property instead. */
	public function getPartyIdentification(): PartyIdentification
	{
		return $this->partyIdentification;
	}

	/** @deprecated Method accessors are deprecated, use {@see $partyIdentification} property instead. */
	public function setPartyIdentification(PartyIdentification $partyIdentification): self
	{
		$this->partyIdentification = $partyIdentification;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $partyName} property instead. */
	public function getPartyName(): PartyName
	{
		return $this->partyName;
	}

	/** @deprecated Method accessors are deprecated, use {@see $partyName} property instead. */
	public function setPartyName(PartyName $partyName): self
	{
		$this->partyName = $partyName;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $postalAddress} property instead. */
	public function getPostalAddress(): PostalAddress
	{
		return $this->postalAddress;
	}

	/** @deprecated Method accessors are deprecated, use {@see $postalAddress} property instead. */
	public function setPostalAddress(PostalAddress $postalAddress): self
	{
		$this->postalAddress = $postalAddress;
		return $this;
	}

	/**
	 * @deprecated use getPartyTaxSchemes() instead
	 */
	public function getPartyTaxScheme(): ?PartyTaxScheme
	{
		@trigger_error('Party::getPartyTaxScheme() is deprecated, use Party::getPartyTaxSchemes() instead', E_USER_DEPRECATED);

		if ($this->partyTaxSchemes === null) {
			return null;
		}

		foreach ($this->partyTaxSchemes as $partyTaxScheme) {
			return $partyTaxScheme;
		}

		return null;
	}

	/**
	 * @deprecated use setPartyTaxSchemes() instead
	 */
	public function setPartyTaxScheme(?PartyTaxScheme $partyTaxScheme): self
	{
		@trigger_error('Party::setPartyTaxScheme() is deprecated, use Party::setPartyTaxSchemes() instead', E_USER_DEPRECATED);

		if ($partyTaxScheme === null) {
			$this->partyTaxSchemes = null;
			return $this;
		}

		$this->partyTaxSchemes = new PartyTaxSchemes;
		$this->partyTaxSchemes->add($partyTaxScheme);
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $partyTaxSchemes} property instead. */
	public function getPartyTaxSchemes(): ?PartyTaxSchemes
	{
		return $this->partyTaxSchemes;
	}

	/** @deprecated Method accessors are deprecated, use {@see $partyTaxSchemes} property instead. */
	public function setPartyTaxSchemes(?PartyTaxSchemes $partyTaxSchemes): self
	{
		$this->partyTaxSchemes = $partyTaxSchemes;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $registerIdentification} property instead. */
	public function getRegisterIdentification(): ?RegisterIdentification
	{
		return $this->registerIdentification;
	}

	/** @deprecated Method accessors are deprecated, use {@see $registerIdentification} property instead. */
	public function setRegisterIdentification(?RegisterIdentification $registerIdentification): self
	{
		$this->registerIdentification = $registerIdentification;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $contact} property instead. */
	public function getContact(): ?Contact
	{
		return $this->contact;
	}

	/** @deprecated Method accessors are deprecated, use {@see $contact} property instead. */
	public function setContact(?Contact $contact): self
	{
		$this->contact = $contact;
		return $this;
	}

	public function toArray(): array
	{
		$data = $this->traitToArray();
		$data['partyTaxScheme'] = $this->getPartyTaxScheme()?->toArray();
		$data['partyTaxSchemes'] = $this->getPartyTaxSchemes()?->toArray();
		return $data;
	}

}