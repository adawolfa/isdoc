# ISDOC

This is a PHP library for parsing and generating [ISDOC](http://www.isdoc.cz/) files.

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
    /** @Map("Extensions") */
    private ?MyExtensions $extensions;
}

class MyExtensions
{
    /** @Map("CustomElement") */
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

    if ($supplement instanceof Adawolfa\ISDOC\X\Supplement) {
        
        if (!$supplement->ok) {
            throw new Exception('Digest failed.');
        }
        
        $supplement->saveTo("supplements/{$supplement->filename}");
        
    }

}
~~~