<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC\Invoice\RemoteSupplement;

/** @internal */
final class Utils
{

	public static function detectFormat(string $filename): string
	{
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		return match ($extension) {
			Manager::FORMAT_ISDOCX, Manager::FORMAT_PDF => $extension,
			default                                     => Manager::FORMAT_ISDOC,
		};
	}

	/**
	 * @throws SupplementException
	 */
	public static function checkSupplementDigest(RemoteSupplement $supplement): bool
	{
		switch ($supplement->getDigestMethod()->algorithm) {

			case 'http://www.w3.org/2000/09/xmldsig#sha1':
				$contents = $supplement->getContents();
				return base64_encode(sha1($contents, true)) === $supplement->getDigestValue()
					   || sha1($contents) === strtolower($supplement->getDigestValue());

			default:
				throw SupplementException::unsupportedDigestAlgo($supplement->getFilename(), $supplement->getDigestMethod()->algorithm);

		}
	}

}