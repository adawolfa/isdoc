<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Invoice;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice as Schema;
use Adawolfa\ISDOC\Schema\XMLNamespace;
use Adawolfa\ISDOC\WriterException;
use BcMath\Number;
use DateTimeImmutable;
use Dom\Element;
use Dom\XMLDocument;
use LibXMLError;
use LogicException;
use PHPUnit\Framework\TestCase;

// One inline extension type backs the typed-API variant of the issue #5 test; keep the proof local.
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

/**
 * A typed, foreign-namespaced line extension — the schema-correct equivalent of issue #5's hand-built element.
 * Subclasses the generated {@see Schema\Extensions} carrier and declares its namespace + prefix via the attribute.
 */
#[XMLNamespace('http://www.myCompany.com/isdoc/extensions', prefix: 'ext')]
final class Issue5LineExtensions extends Schema\Extensions
{

	public ?string $zakazka {
		get => $this->node->getString('Zakazka');
		set { $this->node->setString('Zakazka', $value); }
	}

}

/**
 * Regression tests for bugs reported by third parties in the issue tracker.
 *
 * Each test reproduces the scenario from a closed GitHub issue and asserts the *fixed* behavior. 2.0 makes no
 * promise of backward compatibility with the 1.x public API, but every actual bug that was addressed in 1.x must
 * stay addressed here — these tests are the guard rail for that.
 *
 * Issues authored by the maintainer are intentionally excluded; only externally-reported bugs are covered.
 *
 * @see https://github.com/adawolfa/isdoc/issues
 */
final class RegressionTest extends TestCase
{

	/**
	 * Issue #1 — "invoicedQuantity not working"
	 *
	 * Setting both the unit and the amount on a {@see Schema\Quantity} used to drop the amount: the writer emitted
	 * a self-closing `<InvoicedQuantity unitCode="ks"/>` with the `99` gone. The simple-content `content` hook must
	 * serialize the amount as the element's text body and survive a round-trip.
	 *
	 * @see https://github.com/adawolfa/isdoc/issues/1
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testIssue1InvoicedQuantityContentIsSerialized(): void
	{
		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();

		$line = $this->line('1', '100.0', '121.0', '21.0');

		$quantity           = new Schema\Quantity();
		$quantity->unitCode = 'ks';
		$quantity->content  = '99';
		$line->invoicedQuantity = $quantity;

		$invoice->invoiceLines->add($line);
		$this->addTaxTotal($invoice, '100.0', '21.0', '121.0');

		$xml = ISDOC\Manager::create()->writer->xml($invoice);

		// The amount is present as the element body, not dropped into a self-closing tag.
		$this->assertStringContainsString('<InvoicedQuantity unitCode="ks">99</InvoicedQuantity>', $xml);
		$this->assertStringNotContainsString('<InvoicedQuantity unitCode="ks"/>', $xml);

		// And it reads back identically.
		$readBack = ISDOC\Manager::create()->reader->xml($xml);

		/** @var Schema\InvoiceLine $readLine */
		$readLine = iterator_to_array($readBack->invoiceLines)[0];
		$this->assertNotNull($readLine->invoicedQuantity);
		$this->assertSame('99', $readLine->invoicedQuantity->content);
		$this->assertSame('ks', $readLine->invoicedQuantity->unitCode);
	}

	/**
	 * Issue #5 — "XMLS in Extensions"
	 *
	 * Custom, foreign-namespaced extension elements (e.g. `<ext:Zakazka>`) have no generated mapping; 2.0 routes
	 * them through the public `$node` escape hatch instead of bespoke schema classes. Anything attached to an
	 * entity's backing element must survive encoding — including the move across documents when the line is added
	 * to the invoice — and be reachable again on read.
	 *
	 * @see https://github.com/adawolfa/isdoc/issues/5
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testIssue5ForeignNamespacedExtensionSurvivesRoundTrip(): void
	{
		$extensionNs = 'http://www.myCompany.com/isdoc/extensions';

		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();

		$line = $this->line('1', '100.0', '121.0', '21.0');

		// Attach a foreign-namespaced element via the escape hatch, before the line is grafted into the invoice.
		$document = $line->node->dom->ownerDocument;
		$this->assertNotNull($document);
		$extension              = $document->createElementNS($extensionNs, 'ext:Zakazka');
		$extension->textContent = '25/060';
		$line->node->dom->appendChild($extension);

		$invoice->invoiceLines->add($line);
		$this->addTaxTotal($invoice, '100.0', '21.0', '121.0');

		$xml = ISDOC\Manager::create()->writer->xml($invoice);

		$this->assertStringContainsString(
			'<ext:Zakazka xmlns:ext="' . $extensionNs . '">25/060</ext:Zakazka>',
			$xml,
		);

		// The custom element is reachable again through the escape hatch on read.
		$readBack = ISDOC\Manager::create()->reader->xml($xml);

		/** @var Schema\InvoiceLine $readLine */
		$readLine = iterator_to_array($readBack->invoiceLines)[0];

		$found = null;

		foreach ($readLine->node->dom->childNodes as $child) {
			if ($child instanceof Element && $child->localName === 'Zakazka') {
				$found = $child;
			}
		}

		$this->assertNotNull($found);
		$this->assertSame($extensionNs, $found->namespaceURI);
		$this->assertSame('25/060', $found->textContent);
	}

	/**
	 * Issue #5 — "XMLS in Extensions", solved through the typed API rather than the raw `$node` escape hatch.
	 *
	 * The same goal as {@see testIssue5ForeignNamespacedExtensionSurvivesRoundTrip}, but the foreign-namespaced
	 * element is a typed {@see Schema\Extensions} subclass carrying an {@see XMLNamespace} attribute. The library
	 * nests it inside `<Extensions>` — where the XSD's `xs:any` expects user content — so, unlike the bare
	 * escape-hatch placement, the document also *validates against the schema*, which is what the issue asked for.
	 * The block still survives the move across documents when the line is grafted into the invoice.
	 *
	 * @see https://github.com/adawolfa/isdoc/issues/5
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testIssue5ForeignNamespacedExtensionViaTypedApi(): void
	{
		$extensionNs = 'http://www.myCompany.com/isdoc/extensions';

		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();

		$line = $this->line('1', '100.0', '121.0', '21.0');

		// Attach the typed extension before the line is grafted into the invoice, so the wrapper and its foreign
		// child must survive the move across documents — the same stress as the escape-hatch test above.
		$extensions          = new Issue5LineExtensions();
		$extensions->zakazka = '25/060';
		$line->extensions    = $extensions;

		$invoice->invoiceLines->add($line);
		$this->addTaxTotal($invoice, '100.0', '21.0', '121.0');

		$xml = ISDOC\Manager::create()->writer->xml($invoice);

		// Exactly the element issue #5 wanted, now nested inside <Extensions>.
		$this->assertStringContainsString(
			'<ext:Zakazka xmlns:ext="' . $extensionNs . '">25/060</ext:Zakazka>',
			$xml,
		);

		// Because it sits in its schema-sanctioned place, the whole document validates.
		$this->assertSchemaValid($xml);

		// Typed read-back through as(), without touching the escape hatch.
		$readBack = ISDOC\Manager::create()->reader->xml($xml);

		/** @var Schema\InvoiceLine $readLine */
		$readLine = iterator_to_array($readBack->invoiceLines)[0];
		$typed    = $readLine->extensions?->as(Issue5LineExtensions::class);
		$this->assertNotNull($typed);
		$this->assertSame('25/060', $typed->zakazka);
	}

	/**
	 * Issue #6 — "AnonymousCustomerParty being written after AccountingCustomerParty"
	 *
	 * A simplified tax document (type 7) requires `AnonymousCustomerParty`, and the XSD sequence puts it *before*
	 * `AccountingCustomerParty`. The writer used to emit them in declaration order, producing a document the schema
	 * validator rejected. The generated child order must place `AnonymousCustomerParty` first, and the output must
	 * validate against the official XSD.
	 *
	 * @see https://github.com/adawolfa/isdoc/issues/6
	 * @throws WriterException
	 */
	public function testIssue6AnonymousCustomerPartyPrecedesAccountingCustomerParty(): void
	{
		$invoice = $this->invoice();
		$invoice->documentType = Schema\DocumentType::SimplifiedTaxDocument;

		$invoice->accountingCustomerParty = $this->customerParty();

		$anonymousCustomerParty           = new Schema\AnonymousCustomerParty('123');
		$anonymousCustomerParty->idScheme = 'https://www.rfc-editor.org/rfc/rfc9562.html';
		$invoice->anonymousCustomerParty  = $anonymousCustomerParty;

		$invoice->invoiceLines->add($this->line('1', '100.0', '121.0', '21.0'));
		$this->addTaxTotal($invoice, '100.0', '21.0', '121.0');

		$xml = ISDOC\Manager::create()->writer->xml($invoice);

		$anonymousAt  = strpos($xml, '<AnonymousCustomerParty');
		$accountingAt = strpos($xml, '<AccountingCustomerParty');

		$this->assertNotFalse($anonymousAt);
		$this->assertNotFalse($accountingAt);
		$this->assertLessThan(
			$accountingAt,
			$anonymousAt,
			'AnonymousCustomerParty must be serialized before AccountingCustomerParty.',
		);

		// The reported symptom was an XSD validation failure; the fixed output must validate.
		$this->assertSchemaValid($xml);
	}

	/**
	 * Issue #7 — "Zaokrouhlování bcadd"
	 *
	 * {@see Invoice\LegalMonetaryTotal} sums the invoice lines for the tax-exclusive/inclusive totals. The old
	 * implementation used `bcadd()` without a scale, which truncates to whole numbers, so `50.10 + 50.21` was
	 * written as `100`. The {@see Number}-based sum must keep the decimal places of the summands.
	 *
	 * @see https://github.com/adawolfa/isdoc/issues/7
	 * @throws WriterException
	 */
	public function testIssue7LegalMonetaryTotalSumKeepsDecimals(): void
	{
		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();

		// 50.10 + 50.21 = 100.31 (exclusive); 60.62 + 60.75 = 121.37 (inclusive).
		$invoice->invoiceLines->add($this->line('1', '50.10', '60.62', '10.52'));
		$invoice->invoiceLines->add($this->line('2', '50.21', '60.75', '10.54'));

		$this->addTaxTotal($invoice, '100.31', '21.06', '121.37');

		// The computed totals (left unset, so they fall back to the line sums) keep their decimal places.
		$this->assertSame('100.31', (string) $invoice->legalMonetaryTotal->taxExclusiveAmount);
		$this->assertSame('121.37', (string) $invoice->legalMonetaryTotal->taxInclusiveAmount);

		$xml = ISDOC\Manager::create()->writer->xml($invoice);

		$this->assertStringContainsString('<TaxExclusiveAmount>100.31</TaxExclusiveAmount>', $xml);
		$this->assertStringContainsString('<TaxInclusiveAmount>121.37</TaxInclusiveAmount>', $xml);

		// The old bcadd-without-scale bug truncated these to whole numbers.
		$this->assertStringNotContainsString('<TaxExclusiveAmount>100</TaxExclusiveAmount>', $xml);
		$this->assertStringNotContainsString('<TaxInclusiveAmount>121</TaxInclusiveAmount>', $xml);

		$this->assertSchemaValid($xml);
	}

	/**
	 * Issue #8 — "Opomenutí implementace změny standardu od verze 6.0.2 u Party"
	 *
	 * Since standard 6.0.2 a party may carry several `PartyTaxScheme` entries (e.g. invoicing to Slovakia needs a
	 * `TIN` *and* a `VAT` scheme). 1.x modeled it as a single scalar, so only one could be stored. 2.0 exposes a
	 * repeatable {@see Schema\PartyTaxSchemes} collection — multiple schemes must be writable, validate against the
	 * XSD, and read back in document order.
	 *
	 * @see https://github.com/adawolfa/isdoc/issues/8
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testIssue8PartyCarriesMultipleTaxSchemes(): void
	{
		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();

		$partyTaxSchemes = new Schema\PartyTaxSchemes();
		$partyTaxSchemes->add(new Schema\PartyTaxScheme('CZ12345678', 'VAT'));
		$partyTaxSchemes->add(new Schema\PartyTaxScheme('SK12345678', 'TIN'));
		$invoice->accountingSupplierParty->party->partyTaxSchemes = $partyTaxSchemes;

		$invoice->invoiceLines->add($this->line('1', '100.0', '121.0', '21.0'));
		$this->addTaxTotal($invoice, '100.0', '21.0', '121.0');

		$xml = ISDOC\Manager::create()->writer->xml($invoice);

		$this->assertStringContainsString('<CompanyID>CZ12345678</CompanyID>', $xml);
		$this->assertStringContainsString('<CompanyID>SK12345678</CompanyID>', $xml);
		$this->assertSchemaValid($xml);

		// Both schemes round-trip, in document order.
		$readBack = ISDOC\Manager::create()->reader->xml($xml);
		$schemes  = $readBack->accountingSupplierParty->party->partyTaxSchemes;
		$this->assertNotNull($schemes);
		$this->assertCount(2, $schemes);

		/** @var list<Schema\PartyTaxScheme> $list */
		$list = iterator_to_array($schemes);
		$this->assertSame('VAT', $list[0]->taxScheme);
		$this->assertSame('CZ12345678', $list[0]->companyID);
		$this->assertSame('TIN', $list[1]->taxScheme);
		$this->assertSame('SK12345678', $list[1]->companyID);
	}

	private function invoice(): Invoice
	{
		return new Invoice(
			'12345',
			'00000000-0000-0000-0000-000000001234',
			DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16') ?: throw new LogicException(),
			true,
			'CZK',
			$this->supplierParty(),
		);
	}

	private function supplierParty(): Schema\AccountingSupplierParty
	{
		return new Schema\AccountingSupplierParty($this->party('12345678', 'Firma, a. s.'));
	}

	private function customerParty(): Schema\AccountingCustomerParty
	{
		return new Schema\AccountingCustomerParty($this->party('87654321', 'Customer, a. s.'));
	}

	private function party(string $id, string $name): Schema\Party
	{
		return new Schema\Party(
			new Schema\PartyIdentification($id),
			new Schema\PartyName($name),
			new Schema\PostalAddress(
				'Dlouhá',
				'1234',
				'Praha',
				'100 01',
				new Schema\Country('CZ', 'Česká republika'),
			),
		);
	}

	/**
	 * @param numeric-string $extension
	 * @param numeric-string $inclusive
	 * @param numeric-string $tax
	 */
	private function line(string $id, string $extension, string $inclusive, string $tax): Schema\InvoiceLine
	{
		return new Schema\InvoiceLine(
			$id,
			new Number($extension),
			new Number($inclusive),
			new Number($tax),
			new Number($extension),
			new Number($inclusive),
			new Schema\ClassifiedTaxCategory(
				new Number('21'),
				Schema\VATCalculationMethod::FromTheTop,
			),
		);
	}

	/**
	 * @param numeric-string $taxable
	 * @param numeric-string $tax
	 * @param numeric-string $inclusive
	 */
	private function addTaxTotal(Invoice $invoice, string $taxable, string $tax, string $inclusive): void
	{
		$zero = new Number('0.0');

		$invoice->taxTotal->taxAmount = new Number($tax);
		$invoice->taxTotal->add(new Schema\TaxSubTotal(
			new Number($taxable),
			new Number($tax),
			new Number($inclusive),
			$zero,
			$zero,
			$zero,
			$zero,
			$zero,
			$zero,
			new Schema\TaxCategory(new Number('21')),
		));
	}

	private function assertSchemaValid(string $xml): void
	{
		$previous = libxml_use_internal_errors(true);
		libxml_clear_errors();

		try {
			$document = XMLDocument::createFromString($xml);
			$valid    = $document->schemaValidate(__DIR__ . '/../xsd/isdoc-invoice-6.0.2.xsd');
			$errors   = array_map(
				static fn(LibXMLError $error): string => trim($error->message),
				libxml_get_errors(),
			);
		} finally {
			libxml_clear_errors();
			libxml_use_internal_errors($previous);
		}

		$this->assertTrue($valid, "Generated ISDOC is not valid against the schema:\n" . implode("\n", $errors));
	}

}