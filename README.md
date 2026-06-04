# ISDOC

This is a PHP library for parsing and generating [ISDOC](http://www.isdoc.cz/) files.

Supports:

- ISDOC 6.0.2
- ISDOCX (read/write)
- PDF with embedded XML (read/write, requires [smalot/pdfparser](https://github.com/smalot/pdfparser))

## Deprecations and the road to 2.x

**1.6 is a transition release.** It is API and behavior-compatible with 1.5 — no public method, class or constant is removed, and existing code keeps working unchanged. Its sole purpose is to flag the APIs that change in the upcoming **2.x** rewrite.

The following are deprecated:

**1. Method accessors → property access.** The generated `getX()` / `setX()` accessors are deprecated in favor of direct property access (the properties already work today through `Nette\SmartObject`).

~~~php
$category->setPercent('21');   // deprecated
$percent = $category->getPercent();

$category->percent = '21';     // use the property instead
$percent = $category->percent;
~~~

**2. `SCREAMING_CASE` constants → dedicated constant classes.** The `Foo::SOME_CONSTANT_NAME` constants are deprecated in favor of forward-compatible constant classes (which become enums in 2.x).

~~~php
use Adawolfa\ISDOC\Schema\Invoice\ClassifiedTaxCategory;
use Adawolfa\ISDOC\Schema\Invoice\VATCalculationMethod;
use Adawolfa\ISDOC\Manager;
use Adawolfa\ISDOC\Format;

ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP;   // deprecated
VATCalculationMethod::FromTheTop;                             // use this instead

Manager::FORMAT_PDF;  // deprecated
Format::PDF;          // use this instead
~~~

**3. Decimal strings → `BcMath\Number` (PHP 8.4+).** Passing decimal and monetary values as plain strings is deprecated; pass `BcMath\Number` instances instead, which become the value type in 2.x.

~~~php
use BcMath\Number;

new ClassifiedTaxCategory('21', VATCalculationMethod::FromTheTop);              // deprecated on PHP 8.4+
new ClassifiedTaxCategory(new Number('21'), VATCalculationMethod::FromTheTop);  // 2.x-ready
~~~

**4. `toArray()` is going away — with no replacement.** The `Arrayable::toArray()` method (and the `ToArray` trait behind it) is deprecated and **will simply not exist in 2.x**. If you rely on it, capture what you need before upgrading.

How each surfaces:

- Accessors, `SCREAMING_CASE` constants and `toArray()` carry `@deprecated` annotations only — they are reported by your IDE and static analysis (PHPStan), with **no runtime noise**.
- The decimal-string deprecation emits a real `E_USER_DEPRECATED` through [`symfony/deprecation-contracts`](https://github.com/symfony/deprecation-contracts), but **only on PHP 8.4+**, where the `BcMath\Number` class exists. Collect these with a Symfony-style deprecation handler, or filter them through your error-reporting configuration.

In 2.x these become the only supported forms — enum-typed VAT and payment codes, `BcMath\Number` decimals, and plain property access — and the deprecated accessors, constants and `toArray()` are removed.

2.x also swaps out the whole parsing and mapping engine. The public read/write API stays the same (except few edge cases that are retained in 1.x for BC), but if you map your own extensions with `#[Map]`, expect that part to change — details will follow with 2.x.

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

By default, files are deserialized into `Adawolfa\ISDOC\Schema\Invoice`. All code in that namespace is automatically generated from the official XSD schema. You can extend the base `Invoice` class and map your or your vendor's extensions.

~~~php
use Adawolfa\ISDOC\Map;

class MyInvoice extends Adawolfa\ISDOC\Schema\Invoice
{
    #[Map('Extensions')]
    private ?MyExtensions $extensions;
}

class MyExtensions
{
    #[Map('CustomElement')]
    private string $customElement;
}

$invoice = $manager->reader->file('filename.isdoc', MyInvoice::class);
~~~

## Writing files

You should use the decorated `Adawolfa\ISDOC\Invoice` class when creating ISDOC files, as the constructor is more sane and with reasonable defaults. It also takes care of some of the summary fields.

~~~php
$invoice = new ISDOC\Invoice(
    '12345',
    '00000000-0000-0000-0000-000000001234',
    DateTimeImmutable::createFromFormat('Y-m-d', '2021-08-16'),
    false,
    'CZK',
    new ISDOC\Schema\Invoice\AccountingSupplierParty(
        new ISDOC\Schema\Invoice\Party(
            new ISDOC\Schema\Invoice\PartyIdentification('12345678'),
            new ISDOC\Schema\Invoice\PartyName('Firma, a. s.'),
            new ISDOC\Schema\Invoice\PostalAddress(
                'Dlouhá',
                '1234',
                'Praha',
                '100 01',
                new ISDOC\Schema\Invoice\Country('CZ', 'Česká republika')
            )
        )
    )
);

$invoice->invoiceLines->add(
    new ISDOC\Schema\Invoice\InvoiceLine('1', '100.0', '121.0', '21.0', '100.0', '121.0',
        new ISDOC\Schema\Invoice\ClassifiedTaxCategory(
            '21',
            ISDOC\Schema\Invoice\ClassifiedTaxCategory::VAT_CALCULATION_METHOD_FROM_THE_TOP,
        ),
    )
);

$manager->writer->file($invoice, 'filename.isdoc');
~~~

## ISDOCX

ISDOCX files are supported. Either use the `.isdocx` extension or specify the file format when reading/writing.

~~~php
$invoice = $manager->reader->file('filename.isdocx', ISDOC\Schema\Invoice::class, $manager::FORMAT_ISDOCX);
$manager->writer->file('filename.isdocx', $manager::FORMAT_ISDOCX);
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
        
        $supplement->saveTo("supplements/{$supplement->filename}");
        
    }

}
~~~

## PDF

PDF files with embedded ISDOC are supported. Either use the `.pdf` extension or specify the file format when reading/writing.

~~~php
$invoice = $manager->reader->file('filename.pdf', ISDOC\Schema\Invoice::class, $manager::FORMAT_PDF);
$manager->writer->file('filename.pdf', $manager::FORMAT_PDF);
~~~

The PDF itself is added as a supplement automatically when reading. When writing, you need to add the PDF to the supplement list first:

~~~php
$supplements = new SupplementList();
$supplements->add(Adawolfa\ISDOC\Invoice\Supplement::fromPath('invoice.pdf'));
$invoice->supplementsList = $supplements;
$manager->writer->file($invoice, 'filename.pdf');
~~~

The ISDOC will be appended as an embedded file in the resulting PDF, together with any other supplements.

## FAQ

#### I have a non-conforming ISDOC file that's missing a required value.

You might encounter an exception like this:

~~~
Fatal error: Uncaught Adawolfa\ISDOC\Data\ValueException: Value VATApplicable is missing.
~~~

By default, the decoder hydrates (that is, decodes and assigns) all declared properties unless they are not present in the ISDOC file and have a default value. Some values are always supposed to be there, but sometimes they simply aren't because of an incomplete implementation on the issuing side.

One way to get around this is to enable the relaxed hydration mode, which causes the hydrator simply skip such properties.

~~~php
$manager = Adawolfa\ISDOC\Manager::create($skipMissingPrimitiveValuesHydration = true);
$invoice = $manager->reader->file('filename.isdoc');
~~~

Do note, however, that such an object might have uninitialized properties, causing issues later on.