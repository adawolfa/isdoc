<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC\Invoice\RemoteSupplement;
use Adawolfa\ISDOC\XML\Exception as XmlException;

/** @internal */
final class Utils
{

	public static function detectFormat(string $filename): Format
	{
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		return Format::tryFrom($extension) ?? Format::ISDOC;
	}

	/**
	 * Verifies a supplement's bytes against the digest declared alongside them.
	 *
	 * Security: this is an integrity check, not an authenticity one. The digest and the bytes both come from the
	 * same untrusted container, so a match proves only that the attachment was not corrupted in transit — never
	 * that it originates from a trusted party. Callers must not treat a {@code true} result as a trust boundary.
	 * SHA1 is mandated by the ISDOC spec ({@code …xmldsig#sha1}); the limitation is the self-referential check,
	 * not the algorithm.
	 *
	 * @throws SupplementException
	 */
	public static function checkSupplementDigest(RemoteSupplement $supplement): bool
	{
		try {

			switch ($supplement->digestMethod->algorithm) {

				case 'http://www.w3.org/2000/09/xmldsig#sha1':
					$contents = $supplement->contents;
					return base64_encode(sha1($contents, true)) === $supplement->digestValue
						   || sha1($contents) === strtolower($supplement->digestValue);

				default:
					throw SupplementException::unsupportedDigestAlgo($supplement->filename, $supplement->digestMethod->algorithm);

			}

		} /** @noinspection PhpRedundantCatchClauseInspection */ catch (XmlException $exception) {
			throw SupplementException::malformedDigest($exception);
		}
	}

}