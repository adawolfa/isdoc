<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Information about a particular paid proforma invoice.
 *
 * @property string      $id
 * @property string      $variableSymbol
 * @property string|null $depositAmountCurr
 * @property string      $depositAmount
 */
class NonTaxedDeposit implements Arrayable
{

	use SmartObject;
	use ToArray;

	/** Document name, issuer identification of proforma invoice. */
	#[Map('ID')]
	private string $id;

	/** Variable symbol, used when proforma invoice was paid, typically number of the proforma invoice. */
	#[Map('VariableSymbol')]
	private string $variableSymbol;

	/** Deposit in a foreign currency. */
	#[Map('DepositAmountCurr')]
	private ?string $depositAmountCurr = null;

	/** Deposit in a local currency. */
	#[Map('DepositAmount')]
	private string $depositAmount;

	public function __construct(string $id, string $variableSymbol, string $depositAmount)
	{
		$this->setId($id);
		$this->setVariableSymbol($variableSymbol);
		$this->setDepositAmount($depositAmount);
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	public function getVariableSymbol(): string
	{
		return $this->variableSymbol;
	}

	public function setVariableSymbol(string $variableSymbol): self
	{
		$this->variableSymbol = $variableSymbol;
		return $this;
	}

	public function getDepositAmountCurr(): ?string
	{
		return $this->depositAmountCurr;
	}

	public function setDepositAmountCurr(?string $depositAmountCurr): self
	{
		Restriction::decimal($depositAmountCurr);
		$this->depositAmountCurr = $depositAmountCurr;
		return $this;
	}

	public function getDepositAmount(): string
	{
		return $this->depositAmount;
	}

	public function setDepositAmount(string $depositAmount): self
	{
		Restriction::decimal($depositAmount);
		$this->depositAmount = $depositAmount;
		return $this;
	}

}