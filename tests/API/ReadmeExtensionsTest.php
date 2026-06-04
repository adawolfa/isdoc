<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC\API;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice as S;
use Adawolfa\ISDOC\Schema\XMLNamespace;
use Adawolfa\ISDOC\WriterException;
use BcMath\Number;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

// The extension subclasses mirror a real vendor's <Extensions> block; keep the proof local.
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

/**
 * A header extension block in the vendor's own namespace, serialised with a default-namespace declaration
 * (no prefix). Subclassing the generated {@see S\Extensions} base — not the invoice — is the supported pattern,
 * since a base `?Invoice\Extensions $extensions` property cannot be redeclared with a narrower type.
 */
#[XMLNamespace('http://anydomain.cz/branch/developer/head')]
final class HeaderExtensions extends S\Extensions
{

	public ?string $userfieldName {
		get => $this->node->getString('UserfieldName');
		set { $this->node->setString('UserfieldName', $value); }
	}

	public ?string $additionalHeadDiscount {
		get => $this->node->getString('AdditionalHeadDiscount');
		set { $this->node->setString('AdditionalHeadDiscount', $value); }
	}

}

/** A line extension serialised with an explicit prefix — the exact shape requested in issue #5. */
#[XMLNamespace('http://www.myCompany.com/isdoc/extensions', prefix: 'ext')]
final class LineExtensions extends S\Extensions
{

	public ?string $zakazka {
		get => $this->node->getString('Zakazka');
		set { $this->node->setString('Zakazka', $value); }
	}

}

/**
 * Pins the first-class custom-extension surface: a typed, foreign-namespaced {@see S\Extensions} subclass writes,
 * round-trips and serialises in its correct XSD position — both the default-namespace and the prefixed form.
 */
final class ReadmeExtensionsTest extends TestCase
{

	/**
	 * Header extension, default-namespace form: round-trips, sits in its XSD slot, and stays reachable through
	 * the untyped {@code $node} escape hatch.
	 *
	 * @throws ReaderException
	 * @throws WriterException
	 */
	public function testHeaderExtensionDefaultNamespace(): void
	{
		$manager = ISDOC\Manager::create();
		$invoice = $this->minimalInvoice();

		$extensions                         = new HeaderExtensions();
		$extensions->userfieldName          = 'my user data';
		$extensions->additionalHeadDiscount = '10';
		$invoice->extensions                = $extensions;

		// Set then read back goes through the view cache, so it is the very same (typed) object.
		self::assertSame($extensions, $invoice->extensions);

		$xml = $manager->writer->xml($invoice);

		// Foreign default-namespace serialisation — the wrapper stays in the ISDOC namespace, its children do not.
		self::assertStringContainsString(
			'<UserfieldName xmlns="http://anydomain.cz/branch/developer/head">my user data</UserfieldName>',
			$xml,
		);

		// Correct XSD position: <Extensions> between <RefCurrRate> and <AccountingSupplierParty>.
		$refCurrRate = strpos($xml, '<RefCurrRate>');
		$extElement  = strpos($xml, '<Extensions>');
		$supplier    = strpos($xml, '<AccountingSupplierParty>');
		self::assertIsInt($refCurrRate);
		self::assertIsInt($extElement);
		self::assertIsInt($supplier);
		self::assertLessThan($extElement, $refCurrRate);
		self::assertLessThan($supplier, $extElement);

		// Typed read-back of a foreign-namespaced block via the as() helper — no escape hatch needed.
		$read  = $manager->reader->xml($xml);
		$typed = $read->extensions?->as(HeaderExtensions::class);
		self::assertNotNull($typed);
		self::assertSame('my user data', $typed->userfieldName);
		self::assertSame('10', $typed->additionalHeadDiscount);

		// The same content is reachable through the public $node escape hatch (namespace-aware DOM), with no
		// extension class involved at all — the typed surface and the raw surface are two views over one DOM.
		$extensionsDom = $read->extensions?->node->dom;
		self::assertNotNull($extensionsDom);
		$raw = $extensionsDom->getElementsByTagNameNS('http://anydomain.cz/branch/developer/head', 'UserfieldName');
		self::assertSame('my user data', $raw->item(0)?->textContent);
	}

	/**
	 * Line extension, prefixed form: produces exactly the namespaced element from issue #5 and round-trips.
	 *
	 * @throws ReaderException
	 * @throws WriterException
	 */
	public function testLineExtensionPrefixedNamespace(): void
	{
		$manager = ISDOC\Manager::create();
		$invoice = $this->minimalInvoice();

		/** @var S\InvoiceLine $line */
		$line = iterator_to_array($invoice->invoiceLines)[0];

		$lineExtensions          = new LineExtensions();
		$lineExtensions->zakazka = '25/060';
		$line->extensions        = $lineExtensions;

		$xml = $manager->writer->xml($invoice);

		// The exact prefixed element requested in issue #5.
		self::assertStringContainsString(
			'<ext:Zakazka xmlns:ext="http://www.myCompany.com/isdoc/extensions">25/060</ext:Zakazka>',
			$xml,
		);

		$read = $manager->reader->xml($xml);

		/** @var S\InvoiceLine $readLine */
		$readLine = iterator_to_array($read->invoiceLines)[0];
		$typed    = $readLine->extensions?->as(LineExtensions::class);
		self::assertNotNull($typed);
		self::assertSame('25/060', $typed->zakazka);
	}

	private function minimalInvoice(): ISDOC\Invoice
	{
		$invoice = new ISDOC\Invoice(
			'2021-0007',
			'00000000-0000-0000-0000-000000000007',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException(),
			false,
			'CZK',
			new S\AccountingSupplierParty(new S\Party(
				new S\PartyIdentification('12345678'),
				new S\PartyName('Dodavatel, a. s.'),
				new S\PostalAddress('Dlouhá', '1234', 'Praha', '100 01', new S\Country('CZ', 'Česká republika')),
			)),
		);

		$invoice->invoiceLines->add(new S\InvoiceLine(
			'1',
			new Number('100.0'),
			new Number('121.0'),
			new Number('21.0'),
			new Number('100.0'),
			new Number('121.0'),
			new S\ClassifiedTaxCategory(new Number('21'), S\VATCalculationMethod::FromTheTop),
		));

		return $invoice;
	}

}