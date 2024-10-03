<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * XML to Invoice decoder.
 *
 * @internal
 */
final class Decoder
{

	private XmlEncoder $encoder;

	private Hydrator $hydrator;

	public function __construct(XmlEncoder $encoder, Hydrator $hydrator)
	{
		$this->encoder  = $encoder;
		$this->hydrator = $hydrator;
	}

	/**
	 * @template T of Schema\Invoice
	 * @param class-string<T> $class
	 * @return T&Schema\Invoice
	 * @throws DecoderException
	 */
	public function decode(string $xml, string $class = Schema\Invoice::class, callable $hook = null): Schema\Invoice
	{
		$decoded = $this->encoder->decode($xml, $this->encoder::FORMAT);

		if (!is_array($decoded)) {
			throw new DecoderException('XML could not be deserialized into array.');
		}

		try {
			$data = Data::create($decoded);
		} catch (UnexpectedValueException $unexpectedValueException) {
			throw new DecoderException('Failed to decode XML.', 0, $unexpectedValueException);
		}

		try {
			return $this->hydrator->hydrate($data, $class, $hook);
		} catch (Data\Exception $exception) {
			throw new DecoderException('A data error has been encountered.', 0, $exception);
		}
	}

}