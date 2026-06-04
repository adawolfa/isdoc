<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\X;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Decoder;
use Dom\Element;
use Dom\XMLDocument;
use DOMException;
use ZipArchive;

/**
 * ISDOCX reader: extracts the inner ISDOC document from the ZIP and binds its supplements to archive-backed views.
 *
 * @internal
 */
final readonly class Reader
{

	/**
	 * Uncompressed-size cap for the manifest and the ISDOC XML, enforced before the entry is inflated to defend
	 * against a ZIP decompression bomb. 256 KB is the documented "what the XSD itself fits" bound, mirroring the
	 * PDF reader's XML-detection limit.
	 */
	private const int DocumentSizeLimit = 1 << 18;

	public function __construct(private Decoder $decoder)
	{
	}

	/**
	 * @template T of ISDOC\Schema\Invoice
	 * @param class-string<T> $class
	 * @return T&ISDOC\Schema\Invoice
	 * @throws ISDOC\ReaderException
	 */
	public function file(string $filename, string $class = ISDOC\Schema\Invoice::class): ISDOC\Schema\Invoice
	{
		$zip = new ZipArchive();

		if ($zip->open($filename, ZipArchive::RDONLY) !== true) {
			throw ISDOC\ReaderException::zipCouldNotOpen($filename);
		}

		$xml = $this->readXML($zip);

		if ($xml === null) {
			throw ISDOC\ReaderException::zipCouldNotFindISDOC($filename);
		}

		try {
			$invoice = $this->decoder->decode($xml, $class);
		} catch (ISDOC\DecoderException $exception) {
			throw ISDOC\ReaderException::decodeFailureFile($filename, $exception);
		}

		$list = $invoice->node->getChild('SupplementsList');

		if ($list !== null) {
			foreach ($list->getChildren('Supplement') as $supplement) {
				$supplement->bind(Supplement::class)->zip = $zip;
			}
		}

		return $invoice;
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

			if ($name !== false && strcasecmp(pathinfo($name, PATHINFO_EXTENSION), ISDOC\Format::ISDOC->value) === 0) {
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
		$manifest = $this->readEntry($zip, 'manifest.xml');

		if ($manifest === null) {
			return null;
		}

		// Security: reject any DTD outright (no XXE / billion-laughs on attacker-supplied manifests) and keep
		// LIBXML_NOENT / LIBXML_DTDLOAD out of these flags as a second line of defence. See XML\Document::ParseOptions.
		if (ISDOC\XML\Document::declaresDoctype($manifest)) {
			return null;
		}

		try {
			$document = XMLDocument::createFromString($manifest, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
		} /** @noinspection PhpRedundantCatchClauseInspection */ catch (DOMException) {
			return null;
		}

		$root = $document->documentElement;

		if (!$root instanceof Element) {
			return null;
		}

		foreach ($root->childNodes as $node) {

			if (!$node instanceof Element || $node->localName !== 'maindocument') {
				continue;
			}

			$filename = $node->getAttribute('filename');

			if ($filename === null) {
				continue;
			}

			return $this->readEntry($zip, $filename);

		}

		return null;
	}

	/**
	 * Reads an archive entry into memory, rejecting a decompression bomb up front: the uncompressed size is read
	 * from the central directory and checked against {@see self::DocumentSizeLimit} before a single byte is
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

		if ($size > self::DocumentSizeLimit) {
			throw ISDOC\ReaderException::zipEntryTooLarge($name, $size, self::DocumentSizeLimit);
		}

		$contents = $zip->getFromName($name);

		return $contents === false ? null : $contents;
	}

}