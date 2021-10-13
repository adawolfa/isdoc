<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Adawolfa\ISDOC\Reflection\Reflector;
use Doctrine\Common\Annotations\AnnotationReader;
use Nette\SmartObject;
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

	public const
		FORMAT_AUTO   = null,
		FORMAT_ISDOC  = 'isdoc',
		FORMAT_ISDOCX = 'isdocx';

	private Reader $reader;
	private Writer $writer;

	private function __construct(Reader $reader, Writer $writer)
	{
		$this->reader = $reader;
		$this->writer = $writer;
	}

	public function getReader(): Reader
	{
		return $this->reader;
	}

	public function getWriter(): Writer
	{
		return $this->writer;
	}

	public static function create(): self
	{
		$xmlEncoder       = new XmlEncoder([XmlEncoder::FORMAT_OUTPUT => true]);
		$annotationReader = new AnnotationReader;
		$reflector        = new Reflector($annotationReader);
		$hydrator         = new Hydrator($reflector);
		$serializer       = new Serializer($reflector);
		$decoder          = new Decoder($xmlEncoder, $hydrator);
		$encoder          = new Encoder($xmlEncoder, $serializer);
		$xReader          = new X\Reader($xmlEncoder, $decoder);
		$xWriter          = new X\Writer($xmlEncoder, $encoder);
		$reader           = new Reader($decoder, $xReader);
		$writer           = new Writer($encoder, $xWriter);
		return new self($reader, $writer);
	}

}