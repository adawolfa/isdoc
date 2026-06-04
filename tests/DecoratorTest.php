<?php declare(strict_types=1);

namespace Tests\Adawolfa\ISDOC;

use Adawolfa\ISDOC;
use Adawolfa\ISDOC\Invoice;
use Adawolfa\ISDOC\Invoice\InvoiceLine;
use Adawolfa\ISDOC\ReaderException;
use Adawolfa\ISDOC\Schema\Invoice as Schema;
use Adawolfa\ISDOC\WriterException;
use BcMath\Number;
use DateTimeImmutable;
use Dom\XMLDocument;
use LibXMLError;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Testy ručně psaných „decoratorů“ nad generovaným schématem ({@see Invoice\TaxTotal}, {@see Invoice\InvoiceLine}).
 *
 * Decorator je potomek generované schématické třídy, který dodává buď pohodlnější konstruktor, nebo dopočítává
 * hodnoty odvoditelné z jiných částí dokladu — stejně jako {@see Invoice\LegalMonetaryTotal} dopočítává celkové
 * částky z řádků faktury. Terminologie v komentářích vychází přímo z XSD (elementy a jejich české popisy).
 */
final class DecoratorTest extends TestCase
{

	/**
	 * Daňová rekapitulace ({@code <TaxTotal>}): celková daň ({@code <TaxAmount>}) se bez explicitního nastavení
	 * dopočítá jako součet daní v jednotlivých sazbách ({@code <TaxSubTotal>}/{@code <TaxAmount>}).
	 *
	 * Doklad má dvě sazby — 21 % s daní 21.00 a 0 % s daní 0.00 — takže celková daň musí vyjít 21.0. Hodnota se
	 * nikde ručně nenastavuje; čte se přes get-hook (z paměti) a po zápisu i ze samotného XML (po `flush()`).
	 *
	 * @throws WriterException
	 * @throws ReaderException
	 */
	public function testTaxTotalDefaultsToSubTotalSum(): void
	{
		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();
		$invoice->invoiceLines->add($this->line('1', '100.0', '21.0'));

		// Žádné `$invoice->taxTotal->taxAmount = …` — spoléháme na dopočet ze sazeb.
		$invoice->taxTotal->add($this->taxSubTotal('100.0', '21.0', '121.0', '21'));
		$invoice->taxTotal->add($this->taxSubTotal('250.0', '0.0', '250.0', '0'));

		// Get-hook vrací součet daní jednotlivých sazeb (21.0 + 0.0).
		self::assertSame('21.0', (string) $invoice->taxTotal->taxAmount);

		$xml = ISDOC\Manager::create()->writer->xml($invoice);
		$this->assertSchemaValid($xml);

		// Po zápisu je dopočtená celková daň materializovaná v dokladu (čteno zpět z XML).
		$readBack = ISDOC\Manager::create()->reader->xml($xml);
		self::assertSame('21.0', (string) $readBack->taxTotal->taxAmount);
	}

	/**
	 * Součet daní v sazbách si zachovává desetinná místa (obdoba issue #7 u {@see Invoice\LegalMonetaryTotal}).
	 *
	 * Daň v sazbách 10.52 + 10.54 musí dát 21.06, nikoliv zaokrouhlené 21 — {@see Number} při sčítání rozšiřuje
	 * měřítko na nejpřesnější sčítanec.
	 *
	 * @throws WriterException
	 */
	public function testTaxTotalSumKeepsDecimals(): void
	{
		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();
		$invoice->invoiceLines->add($this->line('1', '100.31', '21.06'));

		$invoice->taxTotal->add($this->taxSubTotal('50.10', '10.52', '60.62', '21'));
		$invoice->taxTotal->add($this->taxSubTotal('50.21', '10.54', '60.75', '21'));

		self::assertSame('21.06', (string) $invoice->taxTotal->taxAmount);

		$xml = ISDOC\Manager::create()->writer->xml($invoice);

		$this->assertStringContainsString('<TaxAmount>21.06</TaxAmount>', $xml);
		// Stará chyba „bcadd bez měřítka“ by součet zkrátila na celé číslo.
		$this->assertStringNotContainsString('<TaxAmount>21</TaxAmount>', $xml);
		$this->assertSchemaValid($xml);
	}

	/**
	 * Ručně nastavená celková daň ({@code <TaxAmount>}) má přednost před dopočtem ze sazeb.
	 *
	 * Sazby by daly součet 21.06, ale rekapitulace je z titulu zaokrouhlení vykázána jako 21.00 — explicitní
	 * hodnota se uloží do dokladu a get-hook ji vrací místo součtu.
	 *
	 * @throws WriterException
	 */
	public function testTaxTotalExplicitAmountOverridesSum(): void
	{
		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();
		$invoice->invoiceLines->add($this->line('1', '100.31', '21.06'));

		$invoice->taxTotal->add($this->taxSubTotal('50.10', '10.52', '60.62', '21'));
		$invoice->taxTotal->add($this->taxSubTotal('50.21', '10.54', '60.75', '21'));
		$invoice->taxTotal->taxAmount = new Number('21.00');

		self::assertSame('21.00', (string) $invoice->taxTotal->taxAmount);

		$xml = ISDOC\Manager::create()->writer->xml($invoice);

		$this->assertStringContainsString('<TaxAmount>21.00</TaxAmount>', $xml);
		$this->assertSchemaValid($xml);
	}

	/**
	 * Řádek faktury ({@code <InvoiceLine>}): celková cena včetně daně na řádku ({@code <LineExtensionAmountTaxInclusive>})
	 * se dopočítá jako cena bez daně ({@code <LineExtensionAmount>}) plus částka daně na řádku ({@code <LineExtensionTaxAmount>}).
	 *
	 * Konstruktor decoratoru bere o jednu částku méně než generovaný; cena s daní 121.0 vznikne z 100.0 + 21.0.
	 *
	 * @throws WriterException
	 */
	public function testInvoiceLineDerivesTaxInclusiveTotal(): void
	{
		$line = new InvoiceLine(
			'1',
			new Number('100.0'),  // cena bez daně na řádku
			new Number('21.0'),   // částka daně na řádku
			new Number('100.0'),  // jednotková cena bez daně
			new Number('121.0'),  // jednotková cena s daní
			new Schema\ClassifiedTaxCategory(new Number('21'), Schema\VATCalculationMethod::FromTheTop),
		);

		// Cena s daní na řádku se dopočetla (100.0 + 21.0).
		self::assertSame('121.0', (string) $line->lineExtensionAmountTaxInclusive);

		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();
		$invoice->invoiceLines->add($line);
		$invoice->taxTotal->add($this->taxSubTotal('100.0', '21.0', '121.0', '21'));

		$xml = ISDOC\Manager::create()->writer->xml($invoice);

		$this->assertStringContainsString('<LineExtensionAmountTaxInclusive>121.0</LineExtensionAmountTaxInclusive>', $xml);
		$this->assertSchemaValid($xml);
	}

	/**
	 * Dopočet ceny s daní na řádku si zachovává desetinná místa: 100.31 + 21.06 = 121.37.
	 *
	 * @throws WriterException
	 */
	public function testInvoiceLineDerivedTotalKeepsDecimals(): void
	{
		$line = new InvoiceLine(
			'1',
			new Number('100.31'),
			new Number('21.06'),
			new Number('100.31'),
			new Number('121.37'),
			new Schema\ClassifiedTaxCategory(new Number('21'), Schema\VATCalculationMethod::FromTheTop),
		);

		self::assertSame('121.37', (string) $line->lineExtensionAmountTaxInclusive);

		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();
		$invoice->invoiceLines->add($line);
		$invoice->taxTotal->add($this->taxSubTotal('100.31', '21.06', '121.37', '21'));

		$xml = ISDOC\Manager::create()->writer->xml($invoice);
		$this->assertSchemaValid($xml);
	}

	/**
	 * Integrace obou decoratorů: dopočtená cena s daní na řádku ({@code <LineExtensionAmountTaxInclusive>}) je tím,
	 * co {@see Invoice\LegalMonetaryTotal} sečte do celkové částky s daní ({@code <TaxInclusiveAmount>}).
	 *
	 * Dva řádky 121.0 + 60.5 dají celkovou částku s daní 181.5, aniž by se cena s daní nebo celková rekapitulace
	 * kdekoliv nastavovaly ručně.
	 *
	 * @throws WriterException
	 */
	public function testDecoratedLinesFeedLegalMonetaryTotal(): void
	{
		$invoice = $this->invoice();
		$invoice->accountingCustomerParty = $this->customerParty();

		$invoice->invoiceLines->add(new InvoiceLine(
			'1',
			new Number('100.0'),
			new Number('21.0'),
			new Number('100.0'),
			new Number('121.0'),
			new Schema\ClassifiedTaxCategory(new Number('21'), Schema\VATCalculationMethod::FromTheTop),
		));

		$invoice->invoiceLines->add(new InvoiceLine(
			'2',
			new Number('50.0'),
			new Number('10.5'),
			new Number('50.0'),
			new Number('60.5'),
			new Schema\ClassifiedTaxCategory(new Number('21'), Schema\VATCalculationMethod::FromTheTop),
		));

		$invoice->taxTotal->add($this->taxSubTotal('150.0', '31.5', '181.5', '21'));

		// Součet cen bez daně (100.0 + 50.0) a cen s daní (121.0 + 60.5) — obě ceny s daní jsou dopočtené.
		self::assertSame('150.0', (string) $invoice->legalMonetaryTotal->taxExclusiveAmount);
		self::assertSame('181.5', (string) $invoice->legalMonetaryTotal->taxInclusiveAmount);

		$xml = ISDOC\Manager::create()->writer->xml($invoice);
		$this->assertSchemaValid($xml);
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
	 * Řádek faktury postavený přes generovanou třídu (cenu s daní zadáváme ručně), aby doklad prošel XSD validací.
	 *
	 * @param numeric-string $extension
	 * @param numeric-string $tax
	 */
	private function line(string $id, string $extension, string $tax): Schema\InvoiceLine
	{
		$inclusive = (string) (new Number($extension))->add(new Number($tax));

		return new Schema\InvoiceLine(
			$id,
			new Number($extension),
			new Number($inclusive),
			new Number($tax),
			new Number($extension),
			new Number($inclusive),
			new Schema\ClassifiedTaxCategory(new Number('21'), Schema\VATCalculationMethod::FromTheTop),
		);
	}

	/**
	 * Sumář jedné daňové sazby ({@code <TaxSubTotal>}): základ, daň a částka s daní v dané sazbě; ostatní (již
	 * uplatněno na záloze / rozdíl) jsou nulové.
	 *
	 * @param numeric-string $taxable
	 * @param numeric-string $tax
	 * @param numeric-string $inclusive
	 * @param numeric-string $percent
	 */
	private function taxSubTotal(string $taxable, string $tax, string $inclusive, string $percent): Schema\TaxSubTotal
	{
		$zero = new Number('0.0');

		return new Schema\TaxSubTotal(
			new Number($taxable),
			new Number($tax),
			new Number($inclusive),
			$zero,
			$zero,
			$zero,
			$zero,
			$zero,
			$zero,
			new Schema\TaxCategory(new Number($percent)),
		);
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

		$this->assertTrue($valid, "Vygenerovaný ISDOC není validní proti schématu:\n" . implode("\n", $errors));
	}

}