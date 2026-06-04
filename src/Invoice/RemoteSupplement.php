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

	/**
	 * @throws SupplementException
	 * @deprecated use {@see $ok} instead
	 */
	public function isOk(): bool;

	/** @throws SupplementException */
	public function saveTo(string $filename): void;

	/** @deprecated use {@see $contents} instead */
	public function getContents(): string;

	/** @deprecated use {@see $filename} instead */
	public function getFilename(): string;

	/** @deprecated use {@see $digestMethod} instead */
	public function getDigestMethod(): DigestMethod;

	/** @deprecated use {@see $digestValue} instead */
	public function getDigestValue(): string;

}