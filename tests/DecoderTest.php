<?php

declare(strict_types=1);
namespace Tests\Adawolfa\ISDOC;
use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
use Symfony;
use Adawolfa;
use Doctrine;

final class DecoderTest extends TestCase
{

	use Snapshot;

	public function testSample(): void
	{
		$invoice = Adawolfa\ISDOC\Manager::create()->getReader()->file(__DIR__ . '/fixtures/sample.isdoc');
		$data    = $invoice->toArray();

		self::walkArrayDateToString($data);

		$this->assertInstanceOf(Adawolfa\ISDOC\Schema\Invoice::class, $invoice);
		$this->assertSnapshot('decoder-sample.json', Json::encode($data, Json::PRETTY));
	}

}