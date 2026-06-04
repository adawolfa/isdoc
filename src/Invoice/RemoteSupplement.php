<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\Invoice;

use Adawolfa\ISDOC\Schema\Invoice\DigestMethod;
use Adawolfa\ISDOC\SupplementException;

/**
 * Extracted document attachment.
 */
interface RemoteSupplement
{

	/**
	 * Default cap (32 MiB) on the number of bytes read from a single attachment, applied by {@see getContents()},
	 * {@see getStream()} and the {@see $contents} / {@see $stream} convenience accessors. It bounds how much an
	 * untrusted container — an ISDOCX ZIP entry or a PDF embedded file — can inflate into memory or onto disk: a
	 * tiny archive can declare a multi-gigabyte entry. Generous for a real attachment; call {@see getContents()} /
	 * {@see getStream()} with an explicit limit to raise it (pass {@code null} to disable the cap entirely).
	 */
	public const int SizeLimit = 1 << 25;

	/**
	 * The attachment file name as declared in the document.
	 */
	public string $filename { get; }

	public DigestMethod $digestMethod { get; }

	public string $digestValue { get; }

	/**
	 * Whether the attachment bytes match the digest declared in the document.
	 *
	 * Security: this is an integrity check, not an authenticity one — the digest and the bytes come from the same
	 * untrusted container, so a match proves only that the file was not corrupted in transit, never that it came
	 * from a trusted source. Do not treat {@code true} as a trust boundary.
	 *
	 * @throws SupplementException
	 */
	public bool $ok { get; }

	/**
	 * The attachment bytes, capped at {@see SizeLimit}; shorthand for {@see getContents()}.
	 *
	 * @throws SupplementException
	 */
	public string $contents { get; }

	/**
	 * A read stream over the attachment bytes, capped at {@see SizeLimit}; shorthand for {@see getStream()}. The
	 * caller owns the returned resource and must {@code fclose()} it.
	 *
	 * @var resource
	 * @throws SupplementException
	 */
	public $stream { get; }

	/**
	 * The attachment bytes, refusing to read past {@code $sizeLimit} so an untrusted container cannot inflate into
	 * unbounded memory. The cap defaults to {@see SizeLimit}; pass a higher value to raise it, or {@code null} to
	 * disable it.
	 *
	 * @throws SupplementException
	 */
	public function getContents(?int $sizeLimit = self::SizeLimit): string;

	/**
	 * A read stream over the attachment bytes, refusing to expose more than {@code $sizeLimit} bytes so an untrusted
	 * container cannot inflate into unbounded memory or disk. The cap defaults to {@see SizeLimit}; pass a higher
	 * value to raise it, or {@code null} to disable it. The caller owns the returned resource and must
	 * {@code fclose()} it.
	 *
	 * @return resource
	 * @throws SupplementException
	 */
	public function getStream(?int $sizeLimit = self::SizeLimit);

	/**
	 * Writes the attachment to the given path, refusing to write past {@code $sizeLimit} so an untrusted container
	 * cannot fill the disk. The cap defaults to {@see SizeLimit}; pass a higher value to raise it, or {@code null}
	 * to disable it.
	 *
	 * @throws SupplementException
	 */
	public function saveTo(string $filename, ?int $sizeLimit = self::SizeLimit): void;

}