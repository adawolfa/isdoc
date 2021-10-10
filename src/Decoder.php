<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

final class Decoder
{

	private XmlEncoder $encoder;
	private Hydrator   $hydrator;

	public function __construct(XmlEncoder $encoder, Hydrator $hydrator)
	{
		$this->encoder  = $encoder;
		$this->hydrator = $hydrator;
	}

	/**
	 * @template T
	 * @param class-string<T> $class
	 * @return T
	 * @throws DecoderException
	 */
	public function decode(string $xml, string $class = Schema\Invoice::class): Schema\Invoice
	{
		try {
			$data = Data::create($this->encoder->decode($xml, $this->encoder::FORMAT));
		} catch (UnexpectedValueException $unexpectedValueException) {
			throw new DecoderException('Failed to decode XML.', 0, $unexpectedValueException);
		}

		try {
			return $this->hydrator->hydrate($data, $class);
		} catch (Data\Exception $exception) {
			throw new DecoderException('A data error has been encountered.', 0, $exception);
		}
	}

}