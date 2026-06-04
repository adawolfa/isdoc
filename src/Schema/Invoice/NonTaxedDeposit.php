<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Schema\Invoice;

use Adawolfa\ISDOC\Schema\Backing;
use Adawolfa\ISDOC\Schema\Entity;
use Adawolfa\ISDOC\XML\Exception;
use BcMath\Number;

/**
 * Information about a particular paid proforma invoice.
 */
class NonTaxedDeposit implements Entity
{

	use Backing;

	/** Document name, issuer identification of proforma invoice. */
	public string $id {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('ID');
		set {
			$this->node->setString('ID', $value);
		}
	}

	/** Variable symbol, used when proforma invoice was paid, typically number of the proforma invoice. */
	public string $variableSymbol {
		/** @throws Exception */
		get => $this->node->getStringOrThrow('VariableSymbol');
		set {
			$this->node->setString('VariableSymbol', $value);
		}
	}

	/** Deposit in a foreign currency. */
	public ?Number $depositAmountCurr {
		/** @throws Exception */
		get => $this->node->getNumber('DepositAmountCurr');
		set {
			$this->node->setNumber('DepositAmountCurr', $value);
		}
	}

	/** Deposit in a local currency. */
	public Number $depositAmount {
		/** @throws Exception */
		get => $this->node->getNumberOrThrow('DepositAmount');
		set {
			$this->node->setNumber('DepositAmount', $value);
		}
	}

	public function __construct(
		string $id,
		string $variableSymbol,
		Number $depositAmount,
	)
	{
		$this->id = $id;
		$this->variableSymbol = $variableSymbol;
		$this->depositAmount = $depositAmount;
	}

}