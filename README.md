# ISDOC

This is a PHP library for parsing and generating [ISDOC](http://www.isdoc.cz/) files.

Supports:

- ISDOC 6.0.2
- ISDOCX (read/write)
- PDF with embedded XML (read/write, requires [smalot/pdfparser](https://github.com/smalot/pdfparser))

Requires PHP 8.4 or later.

> See [upgrading from 1.x to 2.0](#upgrading-from-1x-to-20) section below if you are moving from an older version.

## Installation

~~~bash
composer require adawolfa/isdoc
~~~

## Reading files

~~~php
$manager = Adawolfa\ISDOC\Manager::create();
$invoice = $manager->reader->file('filename.isdoc');

print $invoice->id;

foreach ($invoice->invoiceLines as $invoiceLine) {
    print $invoiceLine->note->content;
}
~~~

By default, files are deserialized into `Adawolfa\ISDOC\Schema\Invoice`. All code in that namespace is automatically generated from the official XSD schema.

Schema objects are thin, lazy **views** over the underlying XML document. Every mapped field is a typed [property hook](https://www.php.net/manual/en/language.oop5.property-hooks.php) that reads from (and writes to) the backing element on demand — so `$invoice->id` reads the `<ID>` element the moment you touch it.

### Custom extensions

The `<Extensions>` block (on the invoice header and on each invoice line) holds your or your vendor's own elements. The XSD requires them to live in their own XML namespace. Give them a typed home by **subclassing the generated `Schema\Invoice\Extensions` entity** and declaring that namespace with the `#[XMLNamespace]` attribute — each field is an ordinary property hook:

~~~php
use Adawolfa\ISDOC\Schema\Invoice\Extensions;
use Adawolfa\ISDOC\Schema\XMLNamespace;

#[XMLNamespace('http://www.myCompany.com/isdoc/extensions', prefix: 'ext')]
final class MyExtensions extends Extensions
{
    public ?string $zakazka {
        get => $this->node->getString('Zakazka');
        set { $this->node->setString('Zakazka', $value); }
    }
}
~~~

Attach it to the invoice (or a line) and it serializes in its correct schema position, in your namespace — with `prefix` set you get `<ext:Zakazka xmlns:ext="…">…</ext:Zakazka>`; omit `prefix` for a default-namespace declaration (`<Zakazka xmlns="…">`):

~~~php
$myExtensions = new MyExtensions();
$myExtensions->zakazka = '25/060';
$invoice->extensions = $myExtensions;
~~~

When reading, the generic `$invoice->extensions` re-views as your subclass through `as()`:

~~~php
$invoice = $manager->reader->file('filename.isdoc');
$zakazka = $invoice->extensions?->as(MyExtensions::class)->zakazka;
~~~

For one-off or untyped content you can always skip the subclass and reach the backing element directly through the public `$node` escape hatch — an `Adawolfa\ISDOC\XML\Node`, whose `$node->dom` exposes the raw `Dom\Element`.

## Writing files

You should use the decorated `Adawolfa\ISDOC\Invoice` class when creating ISDOC files, as the constructor is more sane and with reasonable defaults. It also takes care of some of the summary fields.

~~~php
$invoice = new ISDOC\Invoice(
    id: '12345',
    uuid: '00000000-0000-0000-0000-000000001234',
    issueDate: DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16'),
    vatApplicable: false,
    currencyCode: 'CZK',
    accountingSupplierParty: new ISDOC\Schema\Invoice\AccountingSupplierParty(
        party: new ISDOC\Schema\Invoice\Party(
            partyIdentification: new ISDOC\Schema\Invoice\PartyIdentification(id: '12345678'),
            partyName: new ISDOC\Schema\Invoice\PartyName(name: 'Firma, a. s.'),
            postalAddress: new ISDOC\Schema\Invoice\PostalAddress(
                streetName: 'Dlouhá',
                buildingNumber: '1234',
                cityName: 'Praha',
                postalZone: '100 01',
                country: new ISDOC\Schema\Invoice\Country(
                    identificationCode: 'CZ',
                    name: 'Česká republika',
                ),
            ),
        ),
    ),
);

$invoice->invoiceLines->add(
    new ISDOC\Schema\Invoice\InvoiceLine(
        id: '1',
        lineExtensionAmount: new BcMath\Number('100.0'),
        lineExtensionAmountTaxInclusive: new BcMath\Number('121.0'),
        lineExtensionTaxAmount: new BcMath\Number('21.0'),
        unitPrice: new BcMath\Number('100.0'),
        unitPriceTaxInclusive: new BcMath\Number('121.0'),
        classifiedTaxCategory: new ISDOC\Schema\Invoice\ClassifiedTaxCategory(
            percent: new BcMath\Number('21'),
            vatCalculationMethod: ISDOC\Schema\Invoice\VATCalculationMethod::FromTheTop,
        ),
    )
);

$manager->writer->file($invoice, 'filename.isdoc');
~~~

Every monetary and amount field (line amounts, unit prices, tax bases, percentages, the legal monetary totals, …) is a [`BcMath\Number`](https://www.php.net/manual/en/class.bcmath-number.php), so values keep their exact decimal scale and can be added without floating-point error. Reads return a `BcMath\Number`; writes accept one.

### Computed amounts

The decorated `Adawolfa\ISDOC\Invoice` fills in amounts that can be derived, so you don't repeat — or miscompute — them. The `LegalMonetaryTotal` already sums the invoice lines for you; two more decorators cover the tax recap and the lines themselves.

**Tax total.** `$invoice->taxTotal` is an `Adawolfa\ISDOC\Invoice\TaxTotal` whose total tax amount (`<TaxAmount>`) defaults to the sum of the per-rate sub-totals (`<TaxSubTotal>`). Just add the sub-totals — set `taxAmount` explicitly only when you need to override the sum (e.g. a rounded recap).

~~~php
$invoice->taxTotal->add($taxSubTotal);
// $invoice->taxTotal->taxAmount now equals the sum of the added sub-totals.
~~~

**Invoice line.** Use the decorated `Adawolfa\ISDOC\Invoice\InvoiceLine` to drop the redundant tax-inclusive line total — it is derived from the tax-exclusive total plus the line tax (`LineExtensionAmount + LineExtensionTaxAmount`):

~~~php
$invoice->invoiceLines->add(
    new ISDOC\Invoice\InvoiceLine(
        id: '1',
        lineExtensionAmount: new BcMath\Number('100.0'),    // line total without tax
        lineExtensionTaxAmount: new BcMath\Number('21.0'),  // line tax
        unitPrice: new BcMath\Number('100.0'),              // unit price without tax
        unitPriceTaxInclusive: new BcMath\Number('121.0'),  // unit price with tax
        classifiedTaxCategory: new ISDOC\Schema\Invoice\ClassifiedTaxCategory(
            percent: new BcMath\Number('21'),
            vatCalculationMethod: ISDOC\Schema\Invoice\VATCalculationMethod::FromTheTop,
        ),
    )
);
// LineExtensionAmountTaxInclusive is computed once, at construction, as 100.0 + 21.0 = 121.0.
~~~

The derivation stops there on purpose: the unit prices and the VAT rate stay exactly as you pass them, because turning a percentage into an amount needs a rounding policy this library does not impose.

## ISDOCX

ISDOCX files are supported. Either use the `.isdocx` extension or specify the file format when reading/writing.

~~~php
$invoice = $manager->reader->file('filename.isdocx', format: ISDOC\Format::ISDOCX);
$manager->writer->file($invoice, 'filename.isdocx', format: ISDOC\Format::ISDOCX);
~~~

Attachments (a.k.a. supplements) are supported out of box. When generating an ISDOCX file, use `Adawolfa\ISDOC\Invoice\Supplement`:

~~~php
$supplement = Adawolfa\ISDOC\Invoice\Supplement::fromPath('attachment.pdf');
$invoice->supplementsList->add($supplement);
~~~

Digest will be computed and appended automatically (SHA1, no other algorithms are supported as of now).

When reading, a different subclass is being used:

~~~php
foreach ($invoice->supplementsList as $supplement) {

    if ($supplement instanceof Adawolfa\ISDOC\Invoice\RemoteSupplement) {
        
        if (!$supplement->ok) {
            throw new Exception('Digest failed.');
        }
        
        $supplement->saveTo('supplements/' . basename($supplement->filename));
        
    }

}
~~~

> `$supplement->filename` is whatever the document declared and `$supplement->ok` is an *integrity* check (the bytes
> were not corrupted), **not** an authenticity one — do not treat a passing digest as a trust boundary.

Reading an attachment from an untrusted container (an ISDOCX ZIP entry or a PDF embedded file) is capped at **32 MB** by default, so a tiny archive declaring a multi-gigabyte entry cannot exhaust memory. The `$contents` / `$stream` properties apply that default; when 32 MB is not enough (or you want it tighter), call `getContents($sizeLimit)` / `getStream($sizeLimit)` / `saveTo($filename, $sizeLimit)` with your own limit — pass `null` to disable the cap entirely:

~~~php
$bytes  = $supplement->getContents(64 * 1024 * 1024); // allow up to 64 MB
$handle = $supplement->getStream(64 * 1024 * 1024);   // a read stream you must fclose()
~~~

## PDF

PDF files with embedded ISDOC are supported. Either use the `.pdf` extension or specify the file format when reading/writing.

~~~php
$invoice = $manager->reader->file('filename.pdf', format: ISDOC\Format::PDF);
$manager->writer->file($invoice, 'filename.pdf', format: ISDOC\Format::PDF);
~~~

The PDF itself is added as a supplement automatically when reading. When writing, you need to add the PDF to the supplement list first:

~~~php
$supplements = new Adawolfa\ISDOC\Schema\Invoice\SupplementsList();
$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromPath('invoice.pdf'));
$invoice->supplementsList = $supplements;
$manager->writer->file($invoice, 'filename.pdf');
~~~

The ISDOC will be appended as an embedded file in the resulting PDF, together with any other supplements.

## FAQ

#### I have a non-conforming ISDOC file that's missing a required value.

Parsing never fails wholesale. Because schema objects are lazy views, a missing or malformed value raises **only when you access that specific property**, naming the exact path:

~~~
Adawolfa\ISDOC\XML\Exception: Missing required value 'Invoice/VATApplicable'.
~~~

So a file that omits a required value somewhere can still be read for every part you actually touch — only reading the offending field raises. If a value is optional in your use case, simply don't read it, or guard the access:

~~~php
$invoice = $manager->reader->file('filename.isdoc');

print $invoice->id; // fine even if VATApplicable is missing elsewhere

try {
    $vatApplicable = $invoice->vatApplicable;
} catch (Adawolfa\ISDOC\XML\Exception $exception) {
    $vatApplicable = false; // supply your own fallback
}
~~~

> Earlier versions had a `$skipMissingPrimitiveValuesHydration` flag on `Manager::create()` for this; it has been removed. Lenient parsing is now intrinsic and strictness is per-access, so `Manager::create()` takes no arguments.

#### How do I pick one of several `PartyTaxScheme` entries?

A party exposes all of its tax schemes as a collection; filter it for the one you want (the old `preferredTaxScheme` selection has been removed in favor of this):

~~~php
$vat = null;

foreach ($party->partyTaxSchemes ?? [] as $scheme) {
    if (strtoupper($scheme->taxScheme) === 'VAT') {
        $vat = $scheme;
        break;
    }
}
~~~

#### How do I read or write something the schema doesn't map?

The generated `Schema\Invoice` covers the whole ISDOC XSD, but every entity also exposes its backing element through the public **`$node`** property — an `Adawolfa\ISDOC\XML\Node`. It is the escape hatch for anything outside the mapped surface: a non-standard attribute, an element another system added, or content you simply want to reach by hand. The node offers the same namespace-aware accessors the generated property hooks use — a `@`-prefixed name is an **attribute** on the element, a bare name is a **child element**:

~~~php
$invoice = $manager->reader->file('filename.isdoc');

// read
$attr  = $invoice->node->getString('@customAttribute');   // attribute on <Invoice>, or null
$flag  = $invoice->node->getBool('SomeFlag');             // child element <SomeFlag>, or null
$child = $invoice->node->getChild('SomeElement');         // ?Node, null if absent

// write (passing null removes)
$invoice->node->setString('@customAttribute', 'value');
$invoice->node->remove('SomeFlag');
~~~

The supported subset mirrors the mapped fields: the `has()` probe, the `get*`/`set*` scalar accessors (`getString` / `getInt` / `getBool` / `getDate` / `getNumber`, and `getEnum` / `setEnum`), the `$text` simple-content body, `getChild()` / `getChildren()`, and `setChild()` / `addChild()` / `remove()`. These all resolve in the ISDOC namespace (an un-namespaced element matches too). For elements in a **foreign namespace**, use the typed `<Extensions>` carrier instead (see [Custom extensions](#custom-extensions)) — or drop all the way down to the native DOM:

~~~php
$element = $invoice->node->dom;   // Dom\Element — the full native API for anything else
~~~

## Upgrading from 1.x to 2.0

2.0 is a ground-up reimplementation. Internally the schema objects went from eagerly-hydrated DTOs (built on `symfony/serializer` and reflection) to **lazy, typed views over a live `Dom` document** — every field is a property hook that reads and writes the backing XML on demand. The public façade is deliberately unchanged: you still go through `Manager::create()->reader->file()` / `->writer->file()`, the `Format` enum, and the decorated `Invoice` with its computed totals, and the files you read and write are the same. What changed is the *value surface* — how you reach fields, the types they carry, and how errors arrive.

The list below is everything you need to touch, regardless of which 1.x release you start from. Bump the constraint, then run your test suite and work through the failures (static analysis tends to surface them first, runtime second):

~~~bash
composer require adawolfa/isdoc:^2.0
~~~

### 1. Raise your minimum PHP to 8.4

1.x ran on anything from PHP 7.4 (the earliest releases) up to 8.3; 2.0 requires **`php >=8.4`** and drops everything below. Two 8.4 features are load-bearing with no fallback: **property hooks** (the entire field surface is built on them) and the native **`BcMath\Number`** decimal type. Bump your own `composer.json` and CI matrix (the supported matrix is 8.4 / 8.5) before anything else.

### 2. Fewer dependencies to carry

2.0 has **no runtime Composer dependencies** — it parses and serializes with PHP's native `Dom` extension. `symfony/serializer`, `nette/utils` (and, on older 1.x, `doctrine/annotations`) and `ext-simplexml` are gone; the runtime now needs only `ext-bcmath`, `ext-dom`, `ext-zip`, `ext-libxml`. `smalot/pdfparser` stays optional — it is required only for *reading* PDFs with embedded ISDOC. If your own code relied on any of those packages transitively through this library, require them explicitly now.

### 3. Method accessors became properties

Every `getX()` / `setX()` on the schema objects is now a typed property hook. Read and write the property directly; setters no longer return `$this`, so any fluent chains break.

~~~php
// 1.x
$id = $invoice->getId();
$line->setNote($note);

// 2.0
$id = $invoice->id;
$line->note = $note;
~~~

This is mechanical and applies across the whole schema — the two above are just examples. The same change applies to `Manager`: `getReader()` / `getWriter()` are now the readonly properties `$manager->reader` / `$manager->writer`.

### 4. Collections: `toArray()` is gone

The `Arrayable` / `ToArray` mechanism was removed. Collections (`InvoiceLines`, `TaxTotal`, `SupplementsList`, …) are `IteratorAggregate` — iterate them directly, or materialize with `iterator_to_array()`:

~~~php
// 1.x
$lines = $invoice->invoiceLines->toArray();

// 2.0
$lines = iterator_to_array($invoice->invoiceLines);
~~~

### 5. Decimals are `BcMath\Number`, on reads and writes

1.x represented every monetary/amount value as a plain **string** (the very last 1.6 release already accepted `BcMath\Number` on writes). 2.0 uses `BcMath\Number` throughout — line totals, unit prices, tax bases, percentages, the legal monetary totals, deposit amounts, exchange rates. Reads return a `Number`; writes accept one.

~~~php
// 1.x
$payable = $invoice->legalMonetaryTotal->payableAmount;   // '1234.00' (string)

// 2.0
$payable = $invoice->legalMonetaryTotal->payableAmount;   // BcMath\Number
$asString = (string) $payable;                            // '1234.00' — exact scale preserved
$total    = $payable + $other;                            // exact, no floating-point error
~~~

Audit anywhere you concatenated, compared with `===`, `printf`-ed or `json_encode`-d an amount, and adapt it (cast with `(string)`, or keep working with the `Number`). The canonical string form preserves the exact scale, so `(string) $number` is a drop-in for the old value. When building invoices, pass `new BcMath\Number('100.0')` where you used to pass a string or float.

### 6. Constants became backed enums

The `SCREAMING_CASE` class constants are now dedicated **backed enums** in `Adawolfa\ISDOC\Schema\Invoice\`: `DocumentType`, `VATCalculationMethod`, `PaymentMeansCode`, `LocalReverseChargeCode` and `BatchOrSerialNumber`. The matching properties are typed as the enum (no longer `int` / `string`), and constructors and setters take an enum case:

~~~php
// 1.x — int/string class constants
$category->setVatCalculationMethod(Schema\Invoice\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP); // 1
$type = Schema\Invoice::DOCUMENT_TYPE_INVOICE;                                                                 // 1

// 2.0 — enums
$category->vatCalculationMethod = Schema\Invoice\VATCalculationMethod::FromTheTop;
$type = Schema\Invoice\DocumentType::Invoice;
~~~

Note the casing: the VAT acronym is upper-cased (`VATCalculationMethod`, not `Vat…`). The root invoice's document-type enum lives at `Schema\Invoice\DocumentType`.

### 7. `Manager::create()` takes no arguments

Both former parameters are removed. `$skipMissingPrimitiveValuesHydration` is obsolete — lenient parsing is now intrinsic (see §9) — and `$preferredTaxScheme` is superseded by the tax-scheme collection (see §10):

~~~php
$manager = Manager::create();   // 1.x: Manager::create($skip, $preferred)
~~~

### 8. `Format` enum replaces the `Manager::FORMAT_*` constants

File format is now the `Adawolfa\ISDOC\Format` enum:

~~~php
// 1.x
$manager->getReader()->file('invoice.isdocx', Manager::FORMAT_ISDOCX);

// 2.0
$manager->reader->file('invoice.isdocx', Format::ISDOCX);
~~~

Auto-detection (by file extension) is still the default — omit the argument, or pass `null`, in place of the old `Manager::FORMAT_AUTO`.

### 9. Parsing is lazy — errors are per-access, not wholesale

The parsing and mapping engine has been rewritten from scratch. Schema objects are views over the XML, so **reading a file never fails as a whole**. A missing-required or malformed value raises only when you read *that specific property*, as a path-tagged `Adawolfa\ISDOC\XML\Exception`:

~~~
Adawolfa\ISDOC\XML\Exception: Missing required value 'Invoice/VATApplicable'.
~~~

Consequences for your code:

- The entire `Adawolfa\ISDOC\Data\` namespace is removed — `Data\ValueException`, `Data\MissingValueException` and friends are gone. Catch `Adawolfa\ISDOC\XML\Exception` instead.
- A non-conforming file is still fully usable for every field you actually touch; guard only the ones that may be absent. See the [FAQ](#i-have-a-non-conforming-isdoc-file-thats-missing-a-required-value) for the `try`/`catch` pattern.

### 10. No more write-time decimal/enum restriction exceptions

`DecimalRestrictionException` and `EnumerationRestrictionException` are gone. A `BcMath\Number` cannot hold an invalid decimal, and an enum case *is* its own whitelist, so there is nothing to validate on write. A malformed decimal, date or enum can now only arrive from an external document, and it surfaces as an `XML\Exception` the moment you read that field (per §9). The string-shaped restrictions are unchanged: pattern (UUID, version, language id) and length (currency code, issuing system) still throw eagerly on write, under `Adawolfa\ISDOC\LogicException`.

### 11. Party tax schemes are a filterable collection

The singular `$party->partyTaxScheme` and the old `preferredTaxScheme` selection are removed. A party now exposes all of its schemes as a `partyTaxSchemes` collection — filter it for the one you want (see the [FAQ](#how-do-i-pick-one-of-several-partytaxscheme-entries) for the read pattern):

~~~php
$schemes = new Schema\Invoice\PartyTaxSchemes();
$schemes->add(new Schema\Invoice\PartyTaxScheme('CZ12345678', 'VAT'));
$party->partyTaxSchemes = $schemes;
~~~

### 12. Custom extensions: `#[Map]` is gone

The `#[Map]` attribute and the "subclass `Invoice` and map your own properties" mechanism are removed. The `<Extensions>` block (on the invoice header *and* on each invoice line) now has a first-class, subclassable carrier: subclass `Schema\Invoice\Extensions`, declare your XML namespace with `#[XMLNamespace]`, and expose each field as a property hook. See [Custom extensions](#custom-extensions) above for the full example.

### What stays the same

- `Manager::create()->reader->file()` / `->xml()` and `->writer->file()` / `->xml()`, the `Format` enum (`ISDOC` / `ISDOCX` / `PDF`) and extension auto-detection.
- The decorated `Adawolfa\ISDOC\Invoice` constructor and its computed amounts (the `LegalMonetaryTotal` line sums, the `TaxTotal` recap, the line's gross total).
- ISDOCX supplements (`Supplement::fromPath()` / `fromString()`, the `RemoteSupplement` contract, SHA1 digest) and PDF embedding.