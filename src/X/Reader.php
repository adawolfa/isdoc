<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\X;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Decoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use ZipArchive;

/**
 * ISDOCX reader.
 *
 * @internal
 */
final class Reader
{

	/**
	 * Uncompressed-size cap for the manifest and the ISDOC XML, enforced before the entry is inflated to defend
	 * against a ZIP decompression bomb. 256 KB is a generous bound for what the ISDOC/manifest XML actually fits.
	 */
	private const DOCUMENT_SIZE_LIMIT = 1 << 18;

	private XmlEncoder $xmlEncoder;

	private Decoder $decoder;

	public function __construct(XmlEncoder $encoder, Decoder $decoder)
	{
		$this->xmlEncoder = $encoder;
		$this->decoder    = $decoder;
	}

	/**
	 * @template T of ISDOC\Schema\Invoice
	 * @param class-string<T> $class
	 * @return T&ISDOC\Schema\Invoice
	 * @throws ISDOC\ReaderException
	 */
	public function file(string $filename, string $class = ISDOC\Schema\Invoice::class): ISDOC\Schema\Invoice
	{
		$zip = new ZipArchive;

		if ($zip->open($filename, ZipArchive::RDONLY) !== true) {
			throw ISDOC\ReaderException::zipCouldNotOpen($filename);
		}

		$xml = $this->readXML($zip);

		if ($xml === null) {
			throw ISDOC\ReaderException::zipCouldNotFindISDOC($filename);
		}

		try {
			return $this->decoder->decode($xml, $class, $this->createHook($zip));
		} catch (ISDOC\DecoderException $exception) {
			throw ISDOC\ReaderException::decodeFailureFile($filename, $exception);
		}
	}

	private function createHook(ZipArchive $zip): callable
	{
		return function (object $instance) use ($zip): object {

			if (!$instance instanceof ISDOC\Schema\Invoice\Supplement) {
				return $instance;
			}

			$supplement = new Supplement(
				$instance->filename,
				$instance->digestMethod,
				$instance->digestValue,
				$zip,
			);

			$supplement->preview = $instance->preview;
			return $supplement;

		};
	}

	/** @throws ISDOC\ReaderException */
	private function readXML(ZipArchive $zip): ?string
	{
		$fromManifest = $this->readXMLFromManifest($zip);

		if ($fromManifest !== null) {
			return $fromManifest;
		}

		$files = [];

		for ($i = 0; $i < $zip->numFiles; $i++) {

			$name = $zip->getNameIndex($i);

			if ($name !== false && strcasecmp(pathinfo($name, PATHINFO_EXTENSION), ISDOC\Manager::FORMAT_ISDOC) === 0) {
				$files[] = $name;
			}

		}

		if (count($files) !== 1) {
			return null;
		}

		return $this->readEntry($zip, $files[0]);
	}

	/** @throws ISDOC\ReaderException */
	private function readXMLFromManifest(ZipArchive $zip): ?string
	{
		$manifestXML = $this->readEntry($zip, 'manifest.xml');

		if ($manifestXML === null) {
			return null;
		}

		// Security: the manifest is parsed with Symfony's XmlEncoder, which does not enable LIBXML_NOENT and
		// rejects external entities by default — keep it that way (never pass a LOAD_OPTIONS that adds
		// LIBXML_NOENT / LIBXML_DTDLOAD), otherwise an attacker-supplied manifest could mount an XXE attack.
		try {
			$manifest = $this->xmlEncoder->decode($manifestXML, $this->xmlEncoder::FORMAT);
		} catch (UnexpectedValueException) {
			return null;
		}

		if (!is_array($manifest)) {
			return null;
		}

		$filename = $manifest['maindocument']['@filename'] ?? null;

		if (!is_string($filename)) {
			return null;
		}

		return $this->readEntry($zip, $filename);
	}

	/**
	 * Reads an archive entry into memory, rejecting a decompression bomb up front: the uncompressed size is read
	 * from the central directory and checked against {@see self::DOCUMENT_SIZE_LIMIT} before a single byte is
	 * inflated. Returns null when the entry is absent.
	 *
	 * @throws ISDOC\ReaderException
	 */
	private function readEntry(ZipArchive $zip, string $name): ?string
	{
		$size = Zip::entrySize($zip, $name);

		if ($size === null) {
			return null;
		}

		if ($size > self::DOCUMENT_SIZE_LIMIT) {
			throw ISDOC\ReaderException::zipEntryTooLarge($name, $size, self::DOCUMENT_SIZE_LIMIT);
		}

		$xml = $zip->getFromName($name);

		return $xml === false ? null : $xml;
	}

}