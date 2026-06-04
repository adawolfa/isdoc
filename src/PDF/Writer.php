<?php declare(strict_types=1);

namespace Adawolfa\ISDOC\PDF;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Encoder;
use Adawolfa\ISDOC\WriterException;

/**
 * PDF ISDOC writer: appends the encoded ISDOC document and the remaining supplements to a carrier PDF as an
 * incremental update, the first PDF supplement serving as that carrier.
 *
 * @internal
 */
final readonly class Writer
{

	public function __construct(private Encoder $encoder)
	{
	}

	/** @throws ISDOC\WriterException */
	public function file(ISDOC\Schema\Invoice $invoice, string $filename): void
	{
		$carrier   = null;
		$embedded  = [];

		foreach ($invoice->supplementsList ?? [] as $supplement) {

			if (!$supplement instanceof ISDOC\Invoice\Supplement) {
				throw ISDOC\WriterException::unsupportedSupplementType();
			}

			if ($carrier === null && strtolower(pathinfo($supplement->filename, PATHINFO_EXTENSION)) === 'pdf') {
				$carrier = $supplement;
			} else {
				$embedded[] = $supplement;
			}

		}

		if ($carrier === null) {
			throw ISDOC\WriterException::noPdfSupplement();
		}

		$xml = $this->encodeWithoutCarrier($invoice, $carrier);

		$tempFile = tempnam(sys_get_temp_dir(), 'pdf');

		if ($tempFile === false) {
			throw ISDOC\WriterException::couldNotCreateTempFile();
		}

		try {

			if (!copy($carrier->path, $tempFile)) {
				throw ISDOC\WriterException::fileCouldNotWrite($tempFile);
			}

			$this->append($tempFile, $xml, $embedded);

			if (!rename($tempFile, $filename)) {
				throw ISDOC\WriterException::fileCouldNotWrite($filename);
			}

		} finally {
			@unlink($tempFile);
		}
	}

	/**
	 * Encodes the document with the carrier supplement temporarily removed from the list (it is the PDF itself,
	 * not an embedded attachment), restoring it afterwards so the caller's invoice is left untouched.
	 *
	 * @throws WriterException
	 */
	private function encodeWithoutCarrier(ISDOC\Schema\Invoice $invoice, ISDOC\Invoice\Supplement $carrier): string
	{
		$element = $carrier->node->dom;
		$parent  = $element->parentElement;
		$next    = $element->nextElementSibling;

		$parent?->removeChild($element);

		try {
			return $this->encoder->encode($invoice);
		} catch (ISDOC\EncoderException $exception) {
			throw ISDOC\WriterException::encodeFailure($exception);
		} finally {
			$parent?->insertBefore($element, $next);
		}
	}

	/**
	 * @param list<ISDOC\Invoice\Supplement> $supplements
	 * @throws WriterException
	 */
	private function append(string $filename, string $xml, array $supplements): void
	{
		$fd = @fopen($filename, 'a+');

		if ($fd === false) {
			throw ISDOC\WriterException::fileCouldNotWrite($filename);
		}

		try {

			fseek($fd, -(1 << 8), SEEK_END);
			$buf = fread($fd, 1 << 8);

			if ($buf === false) {
				throw ISDOC\WriterException::pdfAppendFailed('read error');
			}

			$pos = strrpos($buf, "startxref\n");

			if ($pos === false) {
				throw ISDOC\WriterException::pdfAppendFailed('startxref not found');
			}

			$startxref = (int) strtok(substr($buf, $pos + 10), "\n");

			if ($startxref <= 0 || $startxref > ftell($fd)) {
				throw ISDOC\WriterException::pdfAppendFailed('startxref out of bounds');
			}

			fseek($fd, $startxref);

			if (fgets($fd) !== "xref\n") {
				throw ISDOC\WriterException::pdfAppendFailed('xref not found');
			}

			$size = null;
			$root = null;

			do {

				$line = fgets($fd);

				if ($line === false) {
					throw ISDOC\WriterException::pdfAppendFailed('read error');
				}

				if ($line !== "trailer\n") {
					continue;
				}

				$buf = fread($fd, 1 << 10);

				if ($buf === false) {
					throw ISDOC\WriterException::pdfAppendFailed('read error');
				}

				$pos = strpos($buf, '/Size ');

				if ($pos === false) {
					throw ISDOC\WriterException::pdfAppendFailed('size not found, small buffer');
				}

				$size = (int) strtok(substr($buf, $pos + 6), " /\n");

				if ($size < 0 || $size > (1 << 20)) {
					throw ISDOC\WriterException::pdfAppendFailed('size out of range');
				}

				$pos = strpos($buf, '/Root ');

				if ($pos === false) {
					throw ISDOC\WriterException::pdfAppendFailed('root not found');
				}

				// The ">" delimiter stops the token at the closing ">>" when /Root is the trailer's last entry,
				// so the indirect reference (e.g. "1 0 R") is never captured together with the dictionary close.
				$root = strtok(substr($buf, $pos + 6), "/\n>");

				if ($root === false) {
					throw ISDOC\WriterException::pdfAppendFailed('root parse error');
				}

				$root = rtrim($root);

			} while (!feof($fd));

			if ($size === null || $root === null) {
				throw ISDOC\WriterException::pdfAppendFailed('trailer not located');
			}

			fseek($fd, 0, SEEK_END);
			$xmlLength = strlen($xml);
			$objPos    = ftell($fd);

			fwrite($fd, "$size 0 obj\n");
			fwrite($fd, "<< /Type /EmbeddedFile /Subtype /text#2Fxml /F (invoice.isdoc) /Length $xmlLength >>\n");
			fwrite($fd, "stream\n");
			fwrite($fd, $xml);
			fwrite($fd, "\nendstream\nendobj\n");

			$positions = [$objPos];
			$num       = $size + 1;

			foreach ($supplements as $supplement) {

				$sfd = fopen($supplement->path, 'r');

				if ($sfd === false) {
					throw ISDOC\WriterException::failedAddSupplement($supplement->path, $supplement->filename);
				}

				try {

					$filesize = filesize($supplement->path);

					if ($filesize === false || $filesize > (1 << 24)) {
						throw ISDOC\WriterException::failedAddSupplement($supplement->path, $supplement->filename);
					}

					$name   = preg_replace('/[^a-zA-Z0-9-.]/', '_', $supplement->filename);
					$objPos = ftell($fd);

					fprintf($fd, "%d 0 obj\n", $num++);
					fprintf($fd, "<< /Type /EmbeddedFile /F (%s) /Length %d >>\n", $name, $filesize);
					fwrite($fd, "stream\n");
					stream_copy_to_stream($sfd, $fd);
					fwrite($fd, "\nendstream\nendobj\n");

				} finally {
					fclose($sfd);
				}

				$positions[] = $objPos;

			}

			$newXRefPos = ftell($fd);
			fprintf($fd, "xref\n%d %d\n", $size, count($positions));

			foreach ($positions as $position) {
				fprintf($fd, "%010d 00000 n \n", $position);
			}

			fwrite($fd, "trailer\n");
			fprintf($fd, "<< /Size %d /Root %s /Prev %d >>\n", $size + count($positions), rtrim($root), $startxref);
			fwrite($fd, "startxref\n");
			fprintf($fd, "%d\n", $newXRefPos);
			fwrite($fd, "%%EOF\n");

		} finally {
			fclose($fd);
		}
	}

}