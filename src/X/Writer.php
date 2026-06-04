<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\X;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Encoder;
use Dom\XMLDocument;
use ZipArchive;

/**
 * ISDOCX writer: packs the encoded ISDOC document, a manifest, and file-backed supplements into a ZIP.
 *
 * @internal
 */
final class Writer
{

	private const string ManifestNamespace = 'http://isdoc.cz/namespace/2013/manifest';

	public function __construct(private readonly Encoder $encoder)
	{
	}

	/** @throws ISDOC\WriterException */
	public function file(ISDOC\Schema\Invoice $invoice, string $filename): void
	{
		$zip = new ZipArchive();

		if ($zip->open($filename, ZipArchive::CREATE) !== true) {
			throw ISDOC\WriterException::zipCouldNotCreate($filename);
		}

		$success = false;

		try {

			try {
				$xml = $this->encoder->encode($invoice);
			} catch (ISDOC\EncoderException $exception) {
				throw ISDOC\WriterException::encodeFailure($exception);
			}

			// Reading the typed invoice id and supplement names to build the archive entries can surface a lazy
			// XML\Exception; wrap it so the writer keeps throwing only WriterException.
			try {

				$isdocFilename = sprintf('%s.isdoc', $invoice->id);
				$zip->addFromString($isdocFilename, $xml);
				$zip->addFromString('manifest.xml', $this->manifest($isdocFilename));

				foreach ($invoice->supplementsList ?? [] as $supplement) {

					if (
						$supplement instanceof ISDOC\Invoice\Supplement
						&& $zip->addFile($supplement->path, $supplement->filename) === false
					) {
						throw ISDOC\WriterException::failedAddSupplement($supplement->path, $supplement->filename);
					}

				}

			} /** @noinspection PhpRedundantCatchClauseInspection */ catch (ISDOC\XML\Exception $exception) {
				throw ISDOC\WriterException::invalidInvoice($exception);
			}

			$success = true;

		} finally {

			// On failure discard the staged entries so close() never flushes a half-written archive, then close
			// unconditionally to release the file handle (and its lock on Windows). The close() return value is
			// intentionally ignored on the failure path so the original exception is not masked.
			if (!$success) {
				$zip->unchangeAll();
				@$zip->close();
			} else {
				$zip->close();
			}

		}
	}

	private function manifest(string $filename): string
	{
		$document = XMLDocument::createEmpty();

		$manifest = $document->createElementNS(self::ManifestNamespace, 'manifest');
		$main     = $document->createElementNS(self::ManifestNamespace, 'maindocument');
		$main->setAttribute('filename', $filename);
		$manifest->appendChild($main);
		$document->appendChild($manifest);

		$document->formatOutput = true;
		$xml = $document->saveXml();

		return $xml === false ? '' : $xml;
	}

}