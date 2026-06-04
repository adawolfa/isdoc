<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Adawolfa\ISDOC\Reflection\Reflector;
use Nette\SmartObject;
use Smalot\PdfParser\Parser;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * ISDOC manager.
 *
 * @property-read Reader $reader
 * @property-read Writer $writer
 */
final class Manager
{

	use SmartObject;

	/** @deprecated use {@see Format::ISDOCX} instead */
	public const string FORMAT_ISDOCX = 'isdocx';

	/** @deprecated use {@see Format::PDF} instead */
	public const string FORMAT_PDF = 'pdf';

	/** @deprecated use {@see Format::ISDOC} instead */
	public const string FORMAT_ISDOC = 'isdoc';

	/** @deprecated use {@code null} instead */
	public const FORMAT_AUTO = null;

	private Reader $reader;

	private Writer $writer;

	private function __construct(Reader $reader, Writer $writer)
	{
		$this->reader = $reader;
		$this->writer = $writer;
	}

	/**
	 * @deprecated Method accessors are deprecated, use {@see $reader} property instead.
	 */
	public function getReader(): Reader
	{
		return $this->reader;
	}

	/**
	 * @deprecated Method accessors are deprecated, use {@see $writer} property instead.
	 */
	public function getWriter(): Writer
	{
		return $this->writer;
	}

	public static function create(
		bool    $skipMissingPrimitiveValuesHydration = false,
		?string $preferredTaxScheme = null,
	): self
	{
		$xmlEncoder = new XmlEncoder([XmlEncoder::FORMAT_OUTPUT => true]);
		$reflector  = new Reflector;
		$hydrator   = new Hydrator($reflector, $skipMissingPrimitiveValuesHydration, $preferredTaxScheme);
		$serializer = new Serializer($reflector);
		$decoder    = new Decoder($xmlEncoder, $hydrator);
		$encoder    = new Encoder($xmlEncoder, $serializer);
		$xReader    = new X\Reader($xmlEncoder, $decoder);
		$xWriter    = new X\Writer($xmlEncoder, $encoder);

		$pdfReader = null;
		$pdfWriter = new PDF\Writer($encoder);

		if (class_exists(Parser::class)) {
			$pdfParser = new Parser();
			$pdfReader = new PDF\Reader($decoder, $pdfParser);
		}

		$reader = new Reader($decoder, $xReader, $pdfReader);
		$writer = new Writer($encoder, $xWriter, $pdfWriter);

		return new self($reader, $writer);
	}

}