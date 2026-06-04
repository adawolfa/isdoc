<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Deprecations;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\Restriction;
use Adawolfa\ISDOC\ToArray;
use BcMath;
use Nette\SmartObject;

/**
 * Information about a particular paid proforma invoice.
 *
 * @property string                    $id
 * @property string                    $variableSymbol
 * @property string|BcMath\Number|null $depositAmountCurr
 * @property string|BcMath\Number      $depositAmount
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

	public function __construct(string $id, string $variableSymbol, string|BcMath\Number $depositAmount)
	{
		$this->setId($id);
		$this->setVariableSymbol($variableSymbol);
		$this->setDepositAmount($depositAmount);
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

	/** @deprecated Method accessors are deprecated, use {@see $variableSymbol} property instead. */
	public function getVariableSymbol(): string
	{
		return $this->variableSymbol;
	}

	/** @deprecated Method accessors are deprecated, use {@see $variableSymbol} property instead. */
	public function setVariableSymbol(string $variableSymbol): self
	{
		$this->variableSymbol = $variableSymbol;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $depositAmountCurr} property instead. */
	public function getDepositAmountCurr(): ?string
	{
		return $this->depositAmountCurr;
	}

	/** @deprecated Method accessors are deprecated, use {@see $depositAmountCurr} property instead. */
	public function setDepositAmountCurr(string|BcMath\Number|null $depositAmountCurr): self
	{
		Deprecations::number($depositAmountCurr);
		$depositAmountCurr = $depositAmountCurr === null ? null : (string) $depositAmountCurr;
		Restriction::decimal($depositAmountCurr);
		$this->depositAmountCurr = $depositAmountCurr;
		return $this;
	}

	/** @deprecated Method accessors are deprecated, use {@see $depositAmount} property instead. */
	public function getDepositAmount(): string
	{
		return $this->depositAmount;
	}

	/** @deprecated Method accessors are deprecated, use {@see $depositAmount} property instead. */
	public function setDepositAmount(string|BcMath\Number $depositAmount): self
	{
		Deprecations::number($depositAmount);
		$depositAmount = (string) $depositAmount;
		Restriction::decimal($depositAmount);
		$this->depositAmount = $depositAmount;
		return $this;
	}

}