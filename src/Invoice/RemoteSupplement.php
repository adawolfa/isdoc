<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use Adawolfa\ISDOC\SupplementException;

/**
 * Extracted document attachment.
 */
interface RemoteSupplement
{

	public string $filename { get; }

	public DigestMethod $digestMethod { get; }

	public string $digestValue { get; }

	/** @throws SupplementException */
	public bool $ok { get; }

	public string $contents { get; }

	/** @throws SupplementException */
	public function saveTo(string $filename): void;

}