<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Schema\Invoice;
use Adawolfa\ISDOC\Arrayable;
use Adawolfa\ISDOC\Map;
use Adawolfa\ISDOC\ToArray;
use Nette\SmartObject;

/**
 * Attachment digest method identification.
 *
 * @property string $algorithm
 */
class DigestMethod implements Arrayable
{

	use SmartObject;
	use ToArray;

	/**
	 * Algorithm identifiers are defined in http://www.w3.org/TR/xmldsig-core/#sec-AlgID.
	 *
	 * @Map("@Algorithm")
	 */
	private string $algorithm;

	public function __construct(string $algorithm)
	{
		$this->setAlgorithm($algorithm);
	}

	public function getAlgorithm(): string
	{
		return $this->algorithm;
	}

	public function setAlgorithm(string $algorithm): self
	{
		$this->algorithm = $algorithm;
		return $this;
	}

}