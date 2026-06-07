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

			if (preg_match('/^(\d+)\s+(\d+)\s+R$/', $root, $rootRef) !== 1) {
				throw ISDOC\WriterException::pdfAppendFailed('root reference not parseable');
			}

			$rootNum = (int) $rootRef[1];
			$rootGen = (int) $rootRef[2];

			// The whole carrier is needed to locate the catalog dictionary (and, when present, the names dictionary
			// it references) so the new name tree can be merged in without dropping the carrier's existing entries.
			$carrier = file_get_contents($filename);

			if ($carrier === false) {
				throw ISDOC\WriterException::pdfAppendFailed('carrier read error');
			}

			fseek($fd, 0, SEEK_END);

			// Object numbers for the appended objects start past the carrier's highest. Each appended object's byte
			// offset is recorded in $xref as [offset, generation] for the new cross-reference section.
			$num  = $size;
			$xref = [];

			// EmbeddedFile streams: the ISDOC document first, then every remaining supplement. Collected as
			// [stream object number, sanitized name, /AFRelationship value] to build the /Filespec objects afterwards.
			/** @var list<array{int, string, string}> $embedded */
			$embedded = [];

			$xmlStreamNum        = $num++;
			$xref[$xmlStreamNum] = [$this->offset($fd), 0];
			$xmlLength           = strlen($xml);

			fwrite($fd, "$xmlStreamNum 0 obj\n");
			fwrite($fd, "<< /Type /EmbeddedFile /Subtype /text#2Fxml /Length $xmlLength >>\n");
			fwrite($fd, "stream\n");
			fwrite($fd, $xml);
			fwrite($fd, "\nendstream\nendobj\n");

			$embedded[] = [$xmlStreamNum, 'invoice.isdoc', '/Alternative'];

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

					$name             = preg_replace('/[^a-zA-Z0-9-.]/', '_', $supplement->filename) ?? 'supplement';
					$streamNum        = $num++;
					$xref[$streamNum] = [$this->offset($fd), 0];

					fprintf($fd, "%d 0 obj\n", $streamNum);
					fprintf($fd, "<< /Type /EmbeddedFile /Length %d >>\n", $filesize);
					fwrite($fd, "stream\n");
					stream_copy_to_stream($sfd, $fd);
					fwrite($fd, "\nendstream\nendobj\n");

					$embedded[] = [$streamNum, $name, '/Supplement'];

				} finally {
					fclose($sfd);
				}

			}

			// One /Filespec per embedded file. The name belongs here (not on the stream), and /EF points at the
			// stream; /AFRelationship associates it for PDF/A-3 / Factur-X-aware tooling. $names maps the name-tree
			// key to the filespec object number; keys must be unique, so sanitized-name collisions get a suffix.
			$names = [];

			foreach ($embedded as [$streamNum, $name, $relationship]) {

				$fsNum        = $num++;
				$xref[$fsNum] = [$this->offset($fd), 0];

				fprintf($fd, "%d 0 obj\n", $fsNum);
				fprintf(
					$fd,
					"<< /Type /Filespec /F (%s) /UF (%s) /EF << /F %d 0 R >> /AFRelationship %s >>\n",
					$name,
					$name,
					$streamNum,
					$relationship,
				);
				fwrite($fd, "endobj\n");

				$key    = $name;
				$suffix = 1;

				while (isset($names[$key])) {
					$key = $name . '-' . (++$suffix);
				}

				$names[$key] = $fsNum;

			}

			// /EmbeddedFiles name-tree leaf: the entries must be sorted ascending by key (byte order).
			ksort($names, SORT_STRING);

			$treeNum        = $num++;
			$xref[$treeNum] = [$this->offset($fd), 0];

			fprintf($fd, "%d 0 obj\n", $treeNum);
			fwrite($fd, '<< /Names [');

			foreach ($names as $key => $fsNum) {
				fprintf($fd, ' (%s) %d 0 R', $key, $fsNum);
			}

			fwrite($fd, " ] >>\nendobj\n");

			// Link the tree into the catalog through /Names → /EmbeddedFiles, re-emitting whichever object actually
			// owns the names dictionary so the carrier's existing /Names entries (e.g. /Dests) are preserved.
			$catalog = $this->objectBody($carrier, $rootNum, $rootGen);

			if ($catalog === null) {
				throw ISDOC\WriterException::pdfAppendFailed('catalog object not found');
			}

			if (preg_match('~/Names\s+(\d+)\s+(\d+)\s+R~', $catalog, $namesRef) === 1) {

				// The catalog references a names dictionary indirectly; extend that object, leaving the catalog as is.
				$reemitNum  = (int) $namesRef[1];
				$reemitGen  = (int) $namesRef[2];
				$reemitBody = $this->objectBody($carrier, $reemitNum, $reemitGen);

				if ($reemitBody === null) {
					throw ISDOC\WriterException::pdfAppendFailed('names object not found');
				}

				if (str_contains($reemitBody, '/EmbeddedFiles')) {
					throw ISDOC\WriterException::pdfAppendFailed('carrier already declares embedded files');
				}

				$reemitBody = $this->injectAfterDictOpen($reemitBody, 0, " /EmbeddedFiles $treeNum 0 R");

			} else {

				if (str_contains($catalog, '/EmbeddedFiles')) {
					throw ISDOC\WriterException::pdfAppendFailed('carrier already declares embedded files');
				}

				$reemitNum = $rootNum;
				$reemitGen = $rootGen;
				$namesPos  = strpos($catalog, '/Names');

				if ($namesPos !== false) {
					// The catalog has an inline /Names dictionary; extend it in place.
					$reemitBody = $this->injectAfterDictOpen($catalog, $namesPos, " /EmbeddedFiles $treeNum 0 R");
				} else {
					// The catalog has no names dictionary; add one carrying just /EmbeddedFiles.
					$reemitBody = $this->injectAfterDictOpen($catalog, 0, " /Names << /EmbeddedFiles $treeNum 0 R >>");
				}

			}

			$xref[$reemitNum] = [$this->offset($fd), $reemitGen];
			fwrite($fd, "$reemitNum $reemitGen obj");
			fwrite($fd, $reemitBody);
			fwrite($fd, "endobj\n");

			// Cross-reference section: contiguous object numbers grouped into subsections (the re-emitted carrier
			// object has a lower number than the appended ones, so it forms its own subsection).
			$newXRefPos = $this->offset($fd);
			fwrite($fd, "xref\n");

			foreach ($this->xrefSubsections($xref) as [$start, $entries]) {
				$this->writeXRefSubsection($fd, $start, $entries);
			}

			fwrite($fd, "trailer\n");
			fprintf($fd, "<< /Size %d /Root %s /Prev %d >>\n", $num, $root, $startxref);
			fwrite($fd, "startxref\n");
			fprintf($fd, "%d\n", $newXRefPos);
			fwrite($fd, "%%EOF\n");

		} finally {
			fclose($fd);
		}
	}

	/**
	 * Returns the body (everything between the {@code obj} keyword and {@code endobj}) of the most recent definition
	 * of the given indirect object, or {@code null} when it cannot be located. The newest definition wins so that a
	 * carrier that is itself an incremental update resolves to its effective object.
	 */
	private function objectBody(string $carrier, int $num, int $gen): ?string
	{
		if (preg_match_all('~(?<![0-9])' . $num . '\s+' . $gen . '\s+obj\b~', $carrier, $matches, PREG_OFFSET_CAPTURE) === 0) {
			return null;
		}

		$last  = $matches[0][count($matches[0]) - 1];
		$start = $last[1] + strlen($last[0]);
		$end   = strpos($carrier, 'endobj', $start);

		if ($end === false) {
			return null;
		}

		return substr($carrier, $start, $end - $start);
	}

	/**
	 * Inserts {@code $fragment} immediately after the first {@code <<} found at or after {@code $from}, i.e. as the
	 * leading entry of that dictionary. Inserting after the opening avoids having to balance the matching {@code >>}.
	 *
	 * @throws WriterException
	 */
	private function injectAfterDictOpen(string $body, int $from, string $fragment): string
	{
		$open = strpos($body, '<<', $from);

		if ($open === false) {
			throw ISDOC\WriterException::pdfAppendFailed('dictionary not found in object');
		}

		return substr($body, 0, $open + 2) . $fragment . substr($body, $open + 2);
	}

	/**
	 * Groups the recorded object offsets into contiguous cross-reference subsections, ascending by object number.
	 *
	 * @param  array<int, array{int, int}>             $xref object number => [offset, generation]
	 * @return list<array{int, list<array{int, int}>}> [first object number, its entries] per subsection
	 */
	private function xrefSubsections(array $xref): array
	{
		ksort($xref);

		// Bucket entries by the first object number of their contiguous run.
		/** @var array<int, list<array{int, int}>> $runs */
		$runs     = [];
		$start    = null; // first object number of the current run
		$expected = null; // next object number that would keep the run contiguous

		foreach ($xref as $objNum => $entry) {

			if ($start === null || $objNum !== $expected) {
				$start = $objNum;
			}

			$runs[$start][] = $entry;
			$expected       = $objNum + 1;

		}

		$subsections = [];

		foreach ($runs as $first => $entries) {
			$subsections[] = [$first, $entries];
		}

		return $subsections;
	}

	/**
	 * Current byte offset of the stream, rejected when it cannot be determined so a bogus xref entry is never written.
	 *
	 * @param resource $fd
	 * @throws WriterException
	 */
	private function offset($fd): int
	{
		$offset = ftell($fd);

		if ($offset === false) {
			throw ISDOC\WriterException::pdfAppendFailed('could not determine stream offset');
		}

		return $offset;
	}

	/**
	 * Writes one xref subsection header followed by its 20-byte entries.
	 *
	 * @param resource              $fd
	 * @param list<array{int, int}> $entries [offset, generation] pairs in ascending object order
	 */
	private function writeXRefSubsection($fd, int $start, array $entries): void
	{
		fprintf($fd, "%d %d\n", $start, count($entries));

		foreach ($entries as [$offset, $gen]) {
			fprintf($fd, "%010d %05d n \n", $offset, $gen);
		}
	}

}