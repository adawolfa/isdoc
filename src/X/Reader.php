<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\X;
use Adawolfa\ISDOC\Decoder;
use Adawolfa\ISDOC;
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

	private XmlEncoder $xmlEncoder;
	private Decoder    $decoder;

	public function __construct(XmlEncoder $encoder, Decoder $decoder)
	{
		$this->xmlEncoder = $encoder;
		$this->decoder    = $decoder;
	}

	/** @throws ISDOC\ReaderException */
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
		return function (object $instance) use($zip): object {

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

	private function readXML(ZipArchive $zip): ?string
	{
		$fromManifest = $this->readXMLFromManifest($zip);

		if ($fromManifest !== null) {
			return $fromManifest;
		}

		$files = [];

		for ($i = 0; $i < $zip->numFiles; $i++) {

			$name = $zip->getNameIndex($i);

			if (strcasecmp(pathinfo($name, PATHINFO_EXTENSION), ISDOC\Manager::FORMAT_ISDOC) === 0) {
				$files[] = $name;
			}

		}

		if (count($files) !== 1) {
			return null;
		}

		return $zip->getFromName($files[0]);
	}

	private function readXMLFromManifest(ZipArchive $zip): ?string
	{
		$manifestXML = $zip->getFromName('manifest.xml');

		if ($manifestXML === false) {
			return null;
		}

		try {
			$manifest = $this->xmlEncoder->decode($manifestXML, $this->xmlEncoder::FORMAT);
		} catch (UnexpectedValueException $exception) {
			return null;
		}

		if (!is_array($manifest)) {
			return null;
		}

		$filename = $manifest['maindocument']['@filename'] ?? null;

		if (!is_string($filename)) {
			return null;
		}

		$xml = $zip->getFromName($filename);

		if ($xml === false) {
			return null;
		}

		return $xml;
	}

}