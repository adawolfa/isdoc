<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use PHPUnit\Framework\TestCase;
use Symfony;
use Adawolfa;
use Doctrine;
use DateTimeImmutable;

final class EncoderTest extends TestCase
{

	use Snapshot;

	public function testSample(): void
	{
		$invoice = new Adawolfa\ISDOC\Invoice(
			'12345',
			'00000000-0000-0000-0000-000000001234',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16'),
			false,
			'CZK',
			new Adawolfa\ISDOC\Schema\Invoice\AccountingSupplierParty(
				new Adawolfa\ISDOC\Schema\Invoice\Party(
					new Adawolfa\ISDOC\Schema\Invoice\PartyIdentification('12345678'),
					new Adawolfa\ISDOC\Schema\Invoice\PartyName('Firma, a. s.'),
					new Adawolfa\ISDOC\Schema\Invoice\PostalAddress(
						'Dlouhá',
						'1234',
						'Praha',
						'100 01',
						new Adawolfa\ISDOC\Schema\Invoice\Country('CZ', 'Česká republika')
					)
				)
			)
		);

		$invoice->invoiceLines->add(
			new Adawolfa\ISDOC\Schema\Invoice\InvoiceLine(
				'1',
				'100.0',
				'121.0',
				'21.0',
				'100.0',
				'121.0',
				new Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory(
					'21',
					Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP,
				),
			)
		);

		$encoded = $this->createEncoder()->encode($invoice);
		$this->assertSnapshot('encoder-sample.xml', $encoded);
	}

	private function createEncoder(): Adawolfa\ISDOC\Encoder
	{
		$xmlEncoder       = new Symfony\Component\Serializer\Encoder\XmlEncoder;
		$annotationReader = new Doctrine\Common\Annotations\AnnotationReader;
		$reflector        = new Adawolfa\ISDOC\Reflection\Reflector($annotationReader);
		$serializer       = new Adawolfa\ISDOC\Serializer($reflector);
		return new Adawolfa\ISDOC\Encoder($xmlEncoder, $serializer);
	}

}