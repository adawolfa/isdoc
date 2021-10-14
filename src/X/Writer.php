<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\X;
use Adawolfa\ISDOC\Encoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Adawolfa\ISDOC;
use ZipArchive;

/**
 * ISDOCX writer.
 *
 * @internal
 */
final class Writer
{

	private XmlEncoder $xmlEncoder;
	private Encoder    $encoder;

	public function __construct(XmlEncoder $xmlEncoder, Encoder $encoder)
	{
		$this->xmlEncoder = $xmlEncoder;
		$this->encoder    = $encoder;
	}

	/** @throws ISDOC\WriterException */
	public function file(ISDOC\Schema\Invoice $invoice, string $filename): void
	{
		$zip = new ZipArchive;

		if ($zip->open($filename, ZipArchive::CREATE) === false) {
			throw ISDOC\WriterException::zipCouldNotCreate($filename);
		}

		try {
			$xml = $this->encoder->encode($invoice);
		} catch (ISDOC\EncoderException $exception) {
			throw ISDOC\WriterException::encodeFailure($exception);
		}

		$isdocFileName = sprintf('%s.isdoc', $invoice->id);
		$zip->addFromString($isdocFileName, $xml);
		$zip->addFromString('manifest.xml', $this->createManifest($isdocFileName));

		if ($invoice->supplementsList !== null) {

			foreach ($invoice->supplementsList as $supplement) {

				if ($supplement instanceof ISDOC\Invoice\Supplement) {

					if ($zip->addFile($supplement->path, $supplement->filename) === false) {
						throw ISDOC\WriterException::failedAddSupplement($supplement->path, $supplement->filename);
					}

				}

			}

		}

		$zip->close();
	}

	private function createManifest(string $filename): string
	{
		return $this->xmlEncoder->encode(
			[
				'@xmlns' => 'http://isdoc.cz/namespace/2013/manifest',
				'maindocument' => [
					'@filename' => $filename,
					'#'         => null,
				],
			],
			$this->xmlEncoder::FORMAT,
			[
				$this->xmlEncoder::ROOT_NODE_NAME => 'manifest',
				$this->xmlEncoder::ENCODING       => 'utf-8',
			]
		);
	}

}