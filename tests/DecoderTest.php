<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use PHPUnit\Framework\TestCase;
use Symfony;
use Adawolfa;
use Doctrine;

final class DecoderTest extends TestCase
{

	use Snapshot;

	public function testSample(): void
	{
		$xmlEncoder       = new Symfony\Component\Serializer\Encoder\XmlEncoder;
		$annotationReader = new Doctrine\Common\Annotations\AnnotationReader;
		$reflector        = new Adawolfa\ISDOC\Reflection\Reflector($annotationReader);
		$hydrator         = new Adawolfa\ISDOC\Hydrator($reflector);
		$decoder          = new Adawolfa\ISDOC\Decoder($xmlEncoder, $hydrator);
		$invoice          = $decoder->decode(file_get_contents(__DIR__ . '/fixtures/sample.isdoc'));

		$this->assertInstanceOf(Adawolfa\ISDOC\Schema\Invoice::class, $invoice);
		$this->assertSnapshot('decoder-sample', $invoice->toArray());
	}

}