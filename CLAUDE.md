# CLAUDE.md

Guidance for working in this repository — enough context to understand the design and change it safely without breaking the observable behavior or public API.

## What this is

`adawolfa/isdoc` is a PHP library that **parses and generates ISDOC files**. ISDOC ([isdoc.cz](http://www.isdoc.cz/)) is the Czech national XML format for electronic invoices (namespace `http://isdoc.cz/namespace/2013`, schema version 6.0.2). One root XML element, `<Invoice>`, with a deep tree of strongly-typed child elements.

Three container formats are supported:

- **ISDOC** — the raw XML file (`.isdoc`).
- **ISDOCX** — a ZIP archive (`.isdocx`) containing the ISDOC XML, a `manifest.xml`, and binary attachments ("supplements").
- **PDF** — a PDF (`.pdf`) with the ISDOC XML and supplements embedded as PDF `EmbeddedFile` objects. Reading needs `smalot/pdfparser`; writing is done by hand-appending an incremental PDF update.

Schema objects (`Adawolfa\ISDOC\Schema\*`) are thin, lazy **views** over a backing `Dom\Element`: they read and write the live XML document on demand through property hooks. **Almost all of `src/Schema/` is code-generated from the official XSD** — do not hand-edit it; regenerate instead (see *Code generation*).

## Commands

```bash
composer update                 # library repo; lockfile is git-ignored, so update (not install)
vendor/bin/phpunit              # config in phpunit.xml (testsuite → tests/). 73 tests.
vendor/bin/phpstan              # config in phpstan.neon, level max + strict rules, analyzes src + tests
vendor/bin/phpcs                # config in phpcs.xml, analyzes src + tests
vendor/bin/phpcbf               # auto-fix phpcs violations
bin/xsd-schema-make             # regenerate src/Schema/* from the XSD (see Code generation)
```

- PHP **≥ 8.4** (CI matrix: 8.4 / 8.5). Required ext: `bcmath`, `dom`, `zip`. **Property hooks** (8.4) and the new **`Dom\` API** (`Dom\XMLDocument`/`Dom\Element`) are load-bearing.
- Style (`.editorconfig`): **tabs**, LF line endings, and **no final newline** in `*.php` / `bin/xsd-schema-make` / `composer.json`. `declare(strict_types=1)` on one line with the opening tag. Generated code is normalized by `phpcbf` at the end of generation.

## Architecture

The engine is **lazy and DOM-backed**. The XML stays a `Dom\XMLDocument`; schema objects are typed views over their backing element, reading/writing on demand. Errors are **localized to the field being accessed** — parsing never fails wholesale; a missing-required or malformed value throws only when *that property* is read.

The dependency graph is assembled in one place: **`Manager::create()`** (`src/Manager.php`), the composition root.

```
Manager::create()
  ├─ Reader  ── Decoder ─┐ (Document::parse → Node->bind)     ┌── Encoder ── Writer
  │     ├─ X\Reader      │                                    │   (finalize + Document::serialize)
  │     └─ PDF\Reader*   │                                    ├── X\Writer
  └─ Writer ── Encoder ──┘                                    └── PDF\Writer
     * PDF\Reader is only wired if smalot/pdfparser's Parser class exists.
```

**Public façade** — users only touch these:

- `Manager::create()` → `$manager->reader` / `$manager->writer` (public readonly props). `create()` takes no parameters — lenient parsing is intrinsic and tax schemes are a filterable collection, so there are no knobs to pass.
- `Reader::file($filename, $class = Schema\Invoice::class, ?Format $format = null)` and `Reader::xml($xml, $class)`. Returns `T & Schema\Invoice`.
- `Writer::file($invoice, $filename, ?Format $format = null)` and `Writer::xml($invoice): string`.
- File format is the `Adawolfa\ISDOC\Format` enum (`ISDOC`/`ISDOCX`/`PDF`, backed by `'isdoc'`/`'isdocx'`/`'pdf'`). Auto-detection is the default: pass `null` (the omitted `?Format $format = null`), resolved via `Utils::detectFormat()` (file extension; anything that isn't `isdocx`/`pdf` is `isdoc`).

Everything else in `src/` is `@internal`.

### `XML\Node` — the DOM wrapper (`src/XML/Node.php`)

The single source of truth for value access; a namespace-aware façade over one `Dom\Element`. `@`-prefixed name ⇒ attribute on this element; bare name ⇒ child element. Namespace-tolerant match: `localName` equal AND (`namespaceURI` is the ISDOC ns **or** `null`). Constant `Node::Namespace`.

- Construct: `Node::wrap(Element, $order = [])` (read), `Node::create($name, $order = [])` (fresh detached element in a scratch `Dom\XMLDocument`, explicit tag + order), `Node::entity($class)` (fresh element with the tag + order **inferred** from an entity class — what `Backing` uses, see below), `withOrder($order)`, the read-only `$dom` property (raw `Element`), `getPath(?$name)`. Order inference is exposed as `Node::orderFor($class): list<string>` (cached per class). Construction, ordering and `getPath` are all **`@internal`** (see the public/internal split below).
- Read (return `null` if absent; throw `XML\Exception` only if present-but-malformed): `getString/getInt/getBool/getDate/getNumber(name)`, `getEnum(name, BackedEnumClass::class)`, the `$text` property, `has(name)`. Reader/writer names follow a consistent `get*`/`set*` convention; the two parameter-less accessors are property hooks instead (`$text` body, `$dom` raw element). A **present-but-empty** element reads as `''` (it exists); only an absent one is `null`. Decimals are read with `getNumber()` → `?BcMath\Number` (parsed; a present-but-malformed value throws) and written with `setNumber()` — a `Number` is always well-formed, so there is no set-hook decimal validation. The **required-value** half of the access model is `getStringOrThrow/getIntOrThrow/getBoolOrThrow/getDateOrThrow/getNumberOrThrow(name)`, `getEnumOrThrow(name, Class::class)` and `getChildOrThrow(name, T::class)` (all `@internal`) — each the matching reader `?? throw Exception::missingValue(getPath(…))`, centralized on `Node` so a required get-hook reads as one expression with a `/** @throws Exception */` declared on it (PHPStan reads the hook `@throws`). `getEnum()` reads via `getInt()`/`getString()` per the enum's backing type then `tryFrom()`, throwing a path-tagged `XML\Exception` on a present-but-out-of-range value (so an invalid enum is malformed, like a bad int/date).
- Navigate / bind: `getChild(name, ?class)` → `?Node`/`?T`; `getChildren(name, ?class)` → `list<Node>`/`list<T>`; `ensureChild(name, class): T` (find-or-create the wrapper in order — for append-only collections); `bind(class): T` (cached canonical view); `view(class): T` (fresh, uncached). `ensureChild`/`bind`/`view` are `@internal`.
- Write: `setString/setInt/setBool/setDate(name, ?val)` (null removes), `setEnum(name, ?BackedEnum)` (writes `$value->value`), the `$text` property (assigning `null`/`''` ⇒ self-closing), `setChild(name, Node|Entity|null)`, `addChild(name, Node|Entity)`, `remove(name)`.
- **Public vs `@internal`:** the class itself is *not* `@internal` — it leaks to user code through the public `Entity::$node` escape hatch, so a deliberate subset is supported API for reading/writing unmapped extension content: `has()`, the `get*`/`set*` scalar accessors, `$text`, `getChild()`/`getChildren()`, `setChild()`/`addChild()`/`remove()`, and the raw `$dom`. Everything else is `@internal` engine plumbing — construction (`wrap`/`create`/`entity`), order/namespace inference (`orderFor`/`namespaceFor`/`prefixFor`/`withOrder`/`withNamespace`), binding (`bind`/`view`/`ensureChild`), the required-or-throw readers (`get*OrThrow`/`getChildOrThrow`), reference minting (`getReference`/`setReference`/`finalizeReferences`) and `getPath`.
- Subtle internal behavior: `graft()` does `adoptNode` (a **move**, identity preserved at every depth → no view rebinding), then `Element::rename(NS, tag)` in place (so one type serializes under different tags — `Note`/`VATNote`/`ElectronicPossibilityAgreementReference`), then `insertBefore` at the `position()` dictated by the owning view's inferred child order.

**View cache** (`WeakMap<Element, Entity>`): the same element always maps back to the same `Entity`. Populated on `bind()` (read) **and** on `setChild`/`addChild` (write, keyed by the grafted child's element). This is why a `@ref`-resolved reference returns the very object a collection iteration produced (`assertSame`), and why a user-supplied subclass (decorated `LegalMonetaryTotal`, remote `Supplement`) survives a round-trip through a get-hook. **Inline references and unwrapped collections use `view()` (uncached)** so they don't collide in the cache.

**References** (`*LineReference` wrappers, e.g. `<OrderReference>` in a line): the reference get-hook reads `@ref` → `Node::getReference(class, ref)` resolves by **finding the element with the same `localName` carrying `@id === ref`** anywhere in the document (resolution is `localName`-scoped, so an externally-authored file *may* reuse the same id under `OrderReference` vs `DeliveryNoteReference` and still resolve). No `@ref` ⇒ the wrapper's own children *are* the referenced type, bound inline via `view()`. On write, the set-hook stores the target entity in a private field and records the link in `Node::$pendingReferences` (`WeakMap<wrapper, target>`); `Encoder` calls `Node::finalizeReferences($root)` which mints sequential `@id`/`@ref` pairs from a **single document-scoped counter** (unique per document, counted from 1; the counter is local to the call, so every encode starts over at 1) — **only actually-referenced elements get an id**.

### Read pipeline

`Decoder::decode($xml, $class)` → `XML\Document::parse($xml)` (throws `XML\Exception` only on not-well-formed XML; wrapped in `DecoderException`) → `Node::bind($class)`. That's it — no hydration loop. Values are read lazily through hooks when accessed.

### Write pipeline

`Encoder::encode($invoice)` → if the invoice is `Finalizable` (the decorated `Invoice`), `finalizeForWrite()` flushes computed totals into the DOM → `Node::finalizeReferences($root)` mints reference ids → `XML\Document::serialize($node)` runs `saveXml()` (UTF-8, `formatOutput`, single root `xmlns`).

### Schema base: `Entity` + `Backing` (`src/Schema/`)

No base class. An interface **`Entity`** declaring `public Node $node { get; set; }`, plus a trait **`Backing`** implementing it:

```php
public Node $node {
    get => $this->node ??= Node::entity(self::class);
    set { $this->node = $value->withOrder(Node::orderFor(self::class)); }
}
```

There are **no `Element`/`Order` constants** — both are inferred from the class. The element tag is the class **basename** (true for every type, including the decorated `Invoice`/`LegalMonetaryTotal`/`Supplement`); the child-element order is reconstructed by `Node::orderFor()`, which probes the class once (cached): it `newInstanceWithoutConstructor`s the entity, runs `count()` (for a `Countable` collection, to capture the repeated item element first) and then each public get-hook in declaration order via its `ReflectionMethod`, recording the child element each touches. The recorder lives in `Node::element()` (singular child lookups → ordered members) and `Node::getChildren()` (the collection item only, during `count()`) — so attributes (`@`-names never reach `element()`), simple content, references (`@ref` + `view()`), and unwrapped sibling collections (which read via `getChildren()`, not `element()`) all fall out of the order automatically. `$node` is **public** — the escape hatch for extension elements / non-standard attributes. `Node::bind()` does `newInstanceWithoutConstructor` then `$entity->node = $this`, so generated constructors keep strong required params (binding bypasses the constructor).

**`self::class` (not `static::`) pins inference to the schema class** — inside the trait `self::class` resolves to the class that *uses* `Backing` (the generated schema class), never a decorated subclass, so e.g. `LegalMonetaryTotal`'s overridden `taxExclusiveAmount`/`taxInclusiveAmount` hooks never perturb the inferred order. `Node::create($name, $order)` exists for callers that need an explicit tag/order (string-item collections' `add()`); `Node::entity()` is the inferring path.

**Custom extensions** (`<Extensions>` content, foreign namespaces). `Node` carries a child `$namespace` + optional `$prefix` (both default to the ISDOC namespace / no prefix, so **a normal entity emits no extra namespace declaration**). The generated `Schema\Invoice\Extensions` is the one type that opts out of this default: it implements the marker interface **`Schema\Extension`** and, instead of `Backing`, has a bespoke node hook keyed off **`static::class`** (not `self::class`) — extensions have no decorated-override hazard, so late static binding is correct, and it lets a *user subclass* contribute its own child order **and** namespace. A subclass declares its namespace with the **`#[Schema\XMLNamespace('uri', prefix: …)]`** attribute (the class is `XMLNamespace`, not `Namespace`, which is reserved); `Node::namespaceFor()`/`prefixFor()` read it (cached, gated by `is_a(..., Extension::class)` so non-extensions never pay reflection). `Node::matches()` is namespace-relative; `insert()`/`graft()`-rename build elements in the node's namespace (a `prefix` ⇒ `xmlns:ext="…"`, none ⇒ a default `xmlns="…"`). The wrapper element, minted under the subclass basename/namespace, is renamed to the ISDOC `<Extensions>` by the parent's `setChild('Extensions', …)`. The base `?Invoice\Extensions $extensions` property (on `Invoice` and `InvoiceLine`) puts `<Extensions>` in its XSD-sequence slot for the base *and* any subclass write; since PHP property types are invariant, the typed pattern is to **subclass `Invoice\Extensions`** (not the invoice) and read back via `$invoice->extensions?->as(MyExtensions::class)`.

## Schema classes (`src/Schema/`, generated)

~60 classes under `Adawolfa\ISDOC\Schema\Invoice\*`, plus root `Adawolfa\ISDOC\Schema\Invoice`. Conventions:

- `final`-less `class X implements Entity { use Backing; … }` (non-final so the decorated `Invoice`/`LegalMonetaryTotal`/`Supplement` subclasses can extend them). Each member is a **property hook** delegating to `$this->node`; properties are the only accessor surface — there are no `getX()/setX()` shims.
  - scalar required: `/** @throws Exception */ get => $this->node->getStringOrThrow('Map'); set { RESTRICTIONS $this->node->setString('Map', $value); }` (`getStringOrThrow/getIntOrThrow/getBoolOrThrow/getDateOrThrow` + `set…`; decimals are typed `BcMath\Number`, read/write via `getNumberOrThrow/setNumber` with no `Restriction::decimal` — the type guarantees well-formedness). The `get…OrThrow()` reader is the missing-required throw, hoisted onto `Node` with the hook carrying a `/** @throws Exception */`.
  - scalar optional: nullable type, get is a plain `getString('Map')`/`getNumber('Map')` (no `OrThrow`). The parsing readers (`getInt`/`getBool`/`getDate`/`getNumber`, and `getEnum`) throw `XML\Exception` on a present-but-malformed value, so their get-hook still carries `/** @throws Exception */`; only the never-failing `getString('Map')` reader omits it. attribute: `Map` = `@attr`.
  - complex required/optional: `getChild('Map', T::class)` (required uses `getChildOrThrow('Map', T::class)` with a `/** @throws Exception */` on the get hook); `set { $this->node->setChild('Map', $value); }`.
  - collection child: required uses `ensureChild`, optional uses `getChild`.
- **Restrictions** from the XSD enforced inside set-hooks via `Adawolfa\ISDOC\Restriction` (`length`, `maxLength`, `pattern`). **Enumerations become PHP backed enums**, not restrictions: each enumerated property gets a generated `enum` (int- or string-backed per the XSD base type) in `Schema\Invoice\`, named after the property (`VATCalculationMethod`, `DocumentType`, `PaymentMeansCode`, `LocalReverseChargeCode`, `BatchOrSerialNumber`) with PascalCase cases from the XSD doc text (`VATCalculationMethod::FromTheTop`, `DocumentType::Invoice = 1` … `SimplifiedTaxDocument = 7`). Each case carries a `/** … */` doc comment built from its XSD option description via the same `formatDoc()` (English line, trailing period) used for every other generated doc. The property/constructor type **is** the enum; the set-hook is a plain `setEnum()` with no `Restriction::enumeration` (the enum type is the whitelist). The root `Invoice`'s `documentType` enum is referenced as `Invoice\DocumentType` (root-namespace qualification).
- **Collections** (`InvoiceLines`, `TaxTotal`, `SupplementsList`, …) are `class X implements Entity, IteratorAggregate, Countable { use Backing; }` with generated `getIterator(): Generator` (`yield from $this->node->getChildren('Item', Item::class)`), typed `add()`, `count()`, plus any trailing scalar hooks (`TaxTotal`'s `TaxAmount`, `PaymentMeans`'s `AlternateBankAccounts`). The inferred order is `[Item, …trailing tags]` — the `count()` probe supplies `Item` first, the trailing scalar hooks the rest. String-item collections (`EgovClassifiers`) iterate/append element text.
- **Unwrapped collections** (`PartyTaxSchemes`, from a repeated element nested directly in a parent sequence) share the parent's element rather than wrapping: they get a **bespoke node hook** (`Node::entity(self::class)` get, a plain store-as-is set — no `Backing` trait, no `withOrder`, so they keep the parent's order), and the parent's get-hook binds them via `view()` while the set-hook re-grafts items as siblings. The parent's own order **excludes** the unwrapped item (its `getChildren()` read is never traced as a member).
- **Simple-content elements** (`Note`, `Quantity`) expose a `content` hook (`get => $this->node->text; set { $this->node->text = $value; }`) + attribute hooks.
- References (`OrderLine`, `DeliveryNoteLine`, …) carry a private backing field + a reference hook (see *References* above).

### Decorated / hand-written classes (`src/Invoice/`, `src/X/`, `src/PDF/`, not generated)

- **`Adawolfa\ISDOC\Invoice`** extends `Schema\Invoice` with a sane constructor + defaults (`Version = '6.0.2'`) and `implements Finalizable`. The constructor wires in the decorated `Invoice\TaxTotal` and `Invoice\LegalMonetaryTotal`; `finalizeForWrite()` flushes both (the tax total first, since the monetary total does not depend on it).
- **`Invoice\LegalMonetaryTotal`** overrides the `taxExclusiveAmount`/`taxInclusiveAmount` hooks to default to the **`BcMath\Number`** sum of the invoice lines (read via a back-ref to the invoice) when the element is absent; `flush()` writes the effective values into the DOM at serialization time. Its constructor calls `parent::__construct` with zero for all eight totals, then `remove()`s the two computed amounts from the node so their get-hooks fall back to the line sums (a stored zero would otherwise short-circuit the fallback); `flush()` materializes the effective values back in before serialization. The decorated `Invoice::finalizeForWrite()` calls `flush()`.
- **`Invoice\TaxTotal`** mirrors `LegalMonetaryTotal` one level down: the required `taxAmount` (`<TaxTotal>/<TaxAmount>`, the tax recap total) defaults to the **`BcMath\Number`** sum of its per-rate sub-totals (`<TaxSubTotal>/<TaxAmount>`) when the element is absent. Same constructor trick (`parent::__construct(new Number('0'))` then `remove('TaxAmount')`) and the same `flush()`-on-`finalizeForWrite()` wiring. Lazy-not-eager because the sub-totals are `add()`ed *after* construction. The decorated `Invoice` constructs it by default, so `$invoice->taxTotal->taxAmount` auto-sums unless set explicitly (an explicit value wins). An empty default tax total serializes `<TaxAmount>0</TaxAmount>` (the `Number('0')` sum).
- **`Invoice\InvoiceLine`** is constructor sugar only — no hook override, no `flush`. Its constructor derives the required `lineExtensionAmountTaxInclusive` (gross line total) from `lineExtensionAmount + lineExtensionTaxAmount` (net + tax) and forwards everything to `parent::__construct`, dropping that one redundant argument. This is the *only* rounding-free identity in a line; the unit prices and the VAT percent stay as supplied, because turning a percentage into an amount needs a rounding policy the library deliberately does not own (the boundary `LegalMonetaryTotal` also respects). Computed once at construction (both inputs are known up front, unlike a `TaxTotal`'s sub-totals), so it is standalone-safe — correct regardless of which invoice wraps it, no `Finalizable` dependency.
- **Supplements:** `Invoice\Supplement` (file-backed, write path; `fromPath()`/`fromString()`, SHA1 digest), the `Invoice\RemoteSupplement` interface, `X\Supplement` (ZIP-backed, read path; bound via `Node::bind` then `setZip()`, case-insensitive entry lookup), `PDF\Supplement` (PDF-embedded). **Only SHA1 digests** (`http://www.w3.org/2000/09/xmldsig#sha1`). `X\Reader` pre-binds each `<Supplement>` to an `X\Supplement` view so the cache returns it on iteration; `PDF\Writer` temporarily detaches the carrier-PDF supplement's element during encode (no `clone` — that would share the backing DOM).

## Code generation (`bin/xsd-schema-make` + `bin/generator/*.php`)

A Symfony Console script that regenerates `src/Schema/` from `xsd/isdoc-invoice-6.0.2.xsd` using `goetas-webservices/xsd-reader` (dev-only). `Generator` walks the XSD into a `ClassModel`/`MemberModel` tree; `Renderer` **template-emits** PHP source as raw strings (so hook order is exactly get-first); `phpcbf` normalises whitespace/imports/commas at the end. There are **no post-generation patches**. The generator:

- Maps XSD simple types → PHP (`date→DateTimeInterface`, `decimal→BcMath\Number`, `integer→int`, `boolean→bool`, `anyURI→?string`, …); resolves the `LocalReverseChargeCodeType` union.
- Detects collections (first element with `max != 1`), simple-content (`ComplexTypeSimpleContent`), restrictions, enumerations (→ synthesized backed `EnumModel`, one file per property, rendered by `Renderer::renderEnum()`), and `*LineReferenceType` → reference hook to the base referenced type.
- Emits **no `Element`/`Order` constants** — the tag and order are inferred at runtime (see `Node::orderFor()`). The generator only has to keep **member declaration order** correct (e.g. the `AnonymousCustomerParty`-before-`AccountingCustomerParty` reorder), since that order is what inference reads back.
- Emits three special cases: the **inline-repeated** `PartyTaxScheme` → synthesized `PartyTaxSchemes` unwrapped collection; reorders **`AnonymousCustomerParty` before `AccountingCustomerParty`** (the customer-party `xs:choice`); and the line-amount fields (`BcMath\Number`) feed `LegalMonetaryTotal`'s `BcMath\Number` sums.
- `xs:any` is skipped — reachable via the public `$node` escape hatch. **`Extensions` is generated as a first-class, subclassable carrier** (see *Custom extensions* below), so it takes its XSD-sequence slot in the inferred order instead of appending.

## Behavioral edge cases (covered by tests in `tests/`)

- **Localized throw on access:** a missing-required or malformed value throws `XML\Exception` *only when accessed*, path-tagged (`DecoderTest::testMissingRequiredRaisesOnlyOnAccess`).
- **Tax schemes:** exposed as a filterable `PartyTaxSchemes` collection — callers filter it for the scheme they want (`DecoderTest::testMultiPartyTaxScheme`).
- **References round-trip:** inline vs IDREF, namespaced ids, identity via the view cache (`ReferenceTest`, `DecoderTest::testNamespacedReferences`/`testSampleNoReference`, `sample-namespaced-references.isdoc`).
- **Present-but-empty elements** read as `''` (so a required-but-empty `<VariableSymbol/>` doesn't throw); empty collections simply emit nothing (no placeholder element).

Tests use a **snapshot** helper (`tests/Snapshot.php`): first run writes `tests/snapshots/*.xml`, later runs assert equality. Delete a snapshot to regenerate it — review snapshot diffs deliberately before re-blessing.

## Repo / branch notes

- `master` is the **2.x line** (HEAD `Introduce 2.0.x`; `composer.json` carries `branch-alias: dev-master → 2.0.x-dev`). The 1.x line lives on the `1.0`–`1.6` branches. `composer.lock` and `vendor/` are both git-ignored — there is no committed lockfile, so use `composer update` (not `install`) to sync deps.
- Exception hierarchy: everything extends `Adawolfa\ISDOC\Exception`; per-stage exceptions (`ReaderException`, `WriterException`, `DecoderException`, `EncoderException`, `SupplementException`, `XML\Exception`, restriction exceptions) use named static constructors.
- phpstan runs at `level: max` + strict rules; a few PHPUnit-idiom rules are ignored scoped to `tests/` in `phpstan.neon`. `phpcs.xml` relaxes a handful of sniffs for the generated wire-name accessors (`getIsds_id`) and long generated doc-comment lines (the soft 120-column guide).
- **Checked-exception enforcement:** `phpstan.neon`'s `exceptions` block treats `Adawolfa\ISDOC\Exception` as the sole *checked* base (the `LogicException` restriction hierarchy and the `RuntimeException` I/O hierarchy stay unchecked) with `implicitThrows: false` + `missingCheckedExceptionInThrows: true` + `tooWideThrowType: true`, so every method that can throw or propagate an `XML\Exception` (et al.) must declare it in `@throws`, and a stale `@throws` is reported. Consequences the code (and generator) honor: optional parsing getters carry `@throws` (above); each pipeline/subsystem boundary **wraps** a lazy `XML\Exception` into its own stage exception so the public contract stays single-typed (`Decoder`→`DecoderException`, `Encoder`→`EncoderException`, `X\Writer`→`WriterException`, `Utils::checkSupplementDigest`→`SupplementException`, …); `tests/` is scope-ignored for `missingType.checkedException` (test methods let exceptions reach PHPUnit). Two `Dom\XMLDocument::createFromString()` `catch (\DOMException)` blocks are scope-ignored as `catch.neverThrown` — the new Dom API throws it at runtime but phpstan's core stubs don't declare it.
