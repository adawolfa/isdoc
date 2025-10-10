<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use Adawolfa\ISDOC\SupplementException;

/**
 * Extracted document attachment.
 *
 * @property-read string       $contents
 * @property-read bool         $ok
 * @property-read string       $filename
 * @property-read DigestMethod $digestMethod
 * @property-read string       $digestValue
 */
interface RemoteSupplement
{

	/** @throws SupplementException */
	public function isOk(): bool;

	/** @throws SupplementException */
	public function saveTo(string $filename): void;

	public function getContents(): string;

	public function getFilename(): string;

	public function getDigestMethod(): DigestMethod;

	public function getDigestValue(): string;

}