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

		$xml = $zip->getFromName($files[0]);

		return $xml === false ? null : $xml;
	}

	private function readXMLFromManifest(ZipArchive $zip): ?string
	{
		$manifest = $zip->getFromName('manifest.xml');

		if ($manifest === false) {
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

			$xml = $zip->getFromName($filename);

			return $xml === false ? null : $xml;

		}

		return null;
	}

}