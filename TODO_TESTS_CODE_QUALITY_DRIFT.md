# Tests and Code Quality Drift

Unit tests and provider classes that drifted from the project rules. Mapped on 2026-11-14.

## How to use this file

- The rules this file enforces:
  - `.claude/rules/testing.md`: unit tests never fake HTTP. They assert on what the provider class builds, through `getUrl()` and `getPayload()`. HTTP faking belongs only in `tests/Feature/`.
  - `.claude/rules/provider-operations.md`: an operation class extends its resource base class, builds `$url` and `$payload` in `build*()` methods, and `execute()` only sends.
- Work one entry at a time, in board order, unless the user picks another order.
- Open questions come in two kinds:
  - **Default:** settled. Apply the answer without asking.
  - No marker: a design decision for the user. Ask before implementing, and do not decide it alone.
- When the user answers a question, write the answer into the entry as a **Default:**.
- Refactors change structure, not behaviour. Feature tests must keep passing without edits, except for tests moved in from `tests/Unit/`.
- Status rules: `Todo`, `In progress`, `Blocked (open questions)`, `Done`. Board and entry statuses must agree. When an entry is `Done`, list the files changed in its Notes.

## How unit tests fake HTTP today

1. Directly: `Http::fake`, `Http::fakeSequence`, `Http::assertSent`, `Http::assertNotSent`, `Http::assertNothingSent`, `Http::assertSentCount`.
2. Through helpers in `tests/Pest.php`: `fakeProviderPaymentMethods`, `fakeProviderClientByVat`, `fakeProviderDueDates`, `fakeItemUpdate`, `fakeItemCreate`.
3. Implicitly: `mockConfiguration(Provider::MOLONI)` (`tests/Pest.php:81`) fakes `grant`, `documentSets/getAll`, `countries/getAll` and `languages/getAll` for every Moloni test. `tests/TestCase.php:19` calls `Http::preventStrayRequests()`.

## Status board

| # | Entry | Status | Notes |
| --- | --- | --- | --- |
| 1 | Request-shape tests use `assertSent` | In progress | Class changes + test rewrites |
| 2 | Response-handling tests live in `tests/Unit/` | In progress | Move to `tests/Feature/` |
| 3 | HTTP layer tests live in `tests/Unit/` | Todo | Move to `tests/Feature/` |
| 4 | Payload building triggers HTTP lookups | Todo | Rule exception in `testing.md` |
| 5 | Provider class structure drift | Todo | Not test-only |

## Entries

### 1. Request-shape tests use `assertSent`

**Status:** In progress

**Problem:** these tests run `execute()` against a fake and check the URL or body with `Http::assertSent`. They should assert on `getUrl()` and `getPayload()` without HTTP.

| Test file | Tests | Added | Class work needed |
| --- | --- | --- | --- |
| `tests/Unit/Client/Get/IdTest.php` | "can set the client id" | 2026-01-19 | `src/Provider/{Moloni,CegidVendus}/Client/Get.php`: add `getUrl()` / `getPayload()` |
| `tests/Unit/Item/Get/PayloadTest.php` | "sends the item id in the request" | 2026-03-17 | `src/Provider/{Moloni,CegidVendus}/Item/Get.php`: Moloni has `getPayload()`; add `$url` on both, `getPayload()` on Vendus |
| `tests/Unit/Client/Delete/PayloadTest.php` | "sends the delete request for the client id" | 2026-11-14 | `src/Provider/{Moloni,CegidVendus}/Client/Delete.php`: extend `Client` base, add `getUrl()` / `getPayload()`; update `ShouldDeleteClient` like `ShouldDeleteItem` |
| `tests/Unit/Invoice/Get/IdTest.php` | "can set the invoice id" | 2026-11-14 | `src/Provider/{Moloni,CegidVendus}/Invoice/Get.php`: add `getUrl()` / `getPayload()` |
| `tests/Unit/Invoice/Pdf/IdTest.php` | "can set the invoice id", "requests the moloni download with the token from the pdf link and the document id" | 2026-11-14 | `src/Provider/{Moloni,CegidVendus}/Invoice/Pdf.php`: extend `Invoice` base, add `getUrl()` / `getPayload()`. The Moloni download is a second request built from the first response |
| `tests/Unit/Invoice/Cancel/PayloadTest.php` | "sends the cancel request for the invoice id", "sends the notes as observation on moloni only", "omits the observation when no notes are set" | 2026-11-14 | `src/Provider/{Moloni,CegidVendus}/Invoice/Cancel.php`: extend `Invoice` base, add `getUrl()` / `getPayload()` |
| `tests/Unit/Invoice/Find/PayloadTest.php` | all 5 tests | 2026-11-14 | None: `getPayload()` exists. Rewrite the tests only (add `getUrl()` if the URL is asserted) |
| `tests/Unit/Providers/Moloni/Country/GetTest.php` | "posts to the countries get all endpoint" | 2026-10-20 | `src/Provider/Moloni/Country/Get.php`: add `getUrl()` |
| `tests/Unit/Providers/Moloni/Language/GetTest.php` | "posts to the languages get all endpoint" | 2026-10-20 | `src/Provider/Moloni/Language/Get.php`: add `getUrl()` |
| `tests/Unit/Providers/Moloni/DocumentSeries/FindTest.php` | "posts to the document sets get all endpoint" | 2026-10-20 | `src/Provider/Moloni/DocumentSeries/Find.php`: add `getUrl()` |

**Reference implementation:** `src/Provider/{Moloni,CegidVendus}/Item/Delete.php` and `tests/Unit/Item/Delete/PayloadTest.php`.

**Open questions:**
1. "sends the notes as observation on moloni only" and "omits the observation when no notes are set" carry the user note that provider-only features should not be supported. Remove the Moloni `observation` mapping, or keep it and only rewrite the tests?
   **Default:** keep the mapping and rewrite the tests to assert `getPayload()`.
2. The Moloni PDF download URL depends on the first response. Should that second step be tested through a feature test only?
   **Default:** yes. The download is part of the whole PDF process, so its test moves to `tests/Feature/`. The unit test only checks that the class builds the correct data to send (`getUrl()` / `getPayload()` of the first request).

**Done when:**
- [x] No test in the table uses `Http::` or a fake helper (the entry 2 tests in the same files still do, until entry 2 moves them)
- [x] Each listed class builds `$url` (and `$payload` where it sends one) in `build*()` methods
- [x] Docs and `docs/api-reference.md` list the new `getUrl()` / `getPayload()` methods on the touched contracts
- [ ] User ran the test suite and checks

**Notes:** code written 2026-11-14, waiting for user review.
- Classes: `src/Provider/{Moloni,CegidVendus}/Client/{Get,Delete}.php`, `src/Provider/{Moloni,CegidVendus}/Item/Get.php`, `src/Provider/{Moloni,CegidVendus}/Invoice/{Get,Pdf,Cancel,Find}.php`, `src/Provider/Moloni/{Country/Get,Language/Get,DocumentSeries/Find}.php`.
- Contracts: `ShouldGetClient`, `ShouldDeleteClient`, `ShouldGetItem`, `ShouldGetInvoice`, `ShouldGetInvoicePdf`, `ShouldCancelInvoice` gained `getUrl()` and `ShouldHavePayload`. `ShouldFindInvoice` gained `getUrl()`.
- Tests: the 10 files in the table, `tests/Feature/Invoice/PdfTest.php` (moved Moloni download test), and `tests/Unit/Client/StructureTest.php` (gets an id, because the id check now runs in the constructor).
- Docs: `docs/api-reference.md`, `docs/clients/deleting-a-client.md`, `docs/invoices/getting-an-invoice.md`, `docs/invoices/cancelling-an-invoice.md`. The "ID is required" error now comes from the facade call, not `execute()`.
- Removed: `buildId()` on Moloni Client Get and Item Get (renamed to `buildPayload()`), and `endpoint()` on Moloni Invoice Find (merged into `buildUrl()`).
- Kept: Moloni `Item\Get::execute()` still sends through the shared `Moloni\Item::getProduct()`, which writes `products/getOne` directly. Moloni `Item\Update` uses it too.
- Beyond the table: `DocumentSeries\Find` also builds its payload (`qty`, `offset`). Both `Invoice\Find` classes build `$url`, because the Moloni test asserts the endpoint.

### 2. Response-handling tests live in `tests/Unit/`

**Status:** In progress

**Problem:** these tests check how a provider response is handled. That needs HTTP, so they belong in `tests/Feature/`.

| Test file | Tests |
| --- | --- |
| `tests/Unit/Client/Get/IdTest.php` | "fails when the provider has no client with the id" |
| `tests/Unit/Invoice/Get/IdTest.php` | "fails when the provider has no invoice with the id" |
| `tests/Unit/Invoice/Pdf/IdTest.php` | "fails when moloni returns an empty pdf link" |
| `tests/Unit/Providers/Moloni/Country/GetTest.php` | "fills the country id and name matching the iso code", "fails when the iso code is not a moloni country" |
| `tests/Unit/Providers/Moloni/Language/GetTest.php` | "fills the language id and name matching the code", "fails when the code is not a moloni language" |
| `tests/Unit/Providers/Moloni/DocumentSeries/FindTest.php` | "returns the default document set id", "returns the first document set id when none is the default" |

**Default:** move each test unchanged into the matching `tests/Feature/<Resource>/` file; create the file if missing. Moloni-only internals go in `tests/Feature/Providers/Moloni/`.

**Done when:**
- [x] Every listed test lives under `tests/Feature/`
- [x] Emptied unit files are deleted (none emptied: each unit file keeps its request-shape test)
- [ ] User ran the test suite and checks

**Notes:** code written 2026-11-14, waiting for user review.
- Moved into: `tests/Feature/Client/GetTest.php`, `tests/Feature/Invoice/GetTest.php`, `tests/Feature/Invoice/PdfTest.php`, and new `tests/Feature/Providers/Moloni/{Country/GetTest,Language/GetTest,DocumentSeries/FindTest}.php`. The new Moloni files carry the source `beforeEach` (`Cache::flush()`, `mockConfiguration(Provider::MOLONI)`).
- Moved out of: `tests/Unit/Client/Get/IdTest.php`, `tests/Unit/Invoice/Get/IdTest.php`, `tests/Unit/Invoice/Pdf/IdTest.php`, `tests/Unit/Providers/Moloni/{Country/GetTest,Language/GetTest,DocumentSeries/FindTest}.php`. Imports made unused were removed.
- The moved PDF test uses `test(...)` to match `tests/Feature/Invoice/PdfTest.php`.
- `tests/Feature/Providers/` gets no Pest group (user decision).

### 3. HTTP layer tests live in `tests/Unit/`

**Status:** Todo

**Problem:** these files test the HTTP layer itself, so faking is the subject of the test.

- `tests/Unit/HttpRequestTest.php`: status codes and error payloads mapped to exceptions.
- `tests/Unit/Providers/RequestAuthenticationTest.php`: request authentication per provider.
- `tests/Unit/Providers/Moloni/AuthenticationTest.php`: OAuth token fetch, URL and caching.
- `tests/Unit/Providers/Moloni/RequestTest.php`: "asks for human readable errors".

**Open questions:**
1. Move these files to `tests/Feature/`, or keep them in `tests/Unit/` and state the exception in `.claude/rules/testing.md`?
   **Default:** move them to `tests/Feature/`. No rule exception.

### 4. Payload building triggers HTTP lookups

**Status:** Todo

**Problem:** some classes send requests while building the payload, so their unit tests cannot avoid fakes. Rewriting the tests alone does not fix this.

| Lookup | Triggered by | Unit files affected |
| --- | --- | --- |
| Moloni due dates (`maturityDates/getAll`) | client create and update, invoice create | `tests/Unit/Client/Create/ProviderData/{PayloadTest,DefaultPayDueTest,CommercialTermsTest,CountryTest,LanguageTest,NumberTest}.php`, `tests/Unit/Client/Update/ProviderData/PayloadTest.php`, `tests/Unit/Invoice/Create/ProviderData/DueDateTest.php` |
| Payment methods | invoice create | `tests/Unit/Invoice/Create/ProviderData/{PaymentTest,DueDateTest,TypeTest,RelatedDocumentTest}.php`, `tests/Unit/Invoice/Create/ProviderData/CreditNote/CreditNoteReasonTest.php` |
| Client by VAT | invoice create | `tests/Unit/Invoice/Create/ProviderData/{ClientTest,TransportTest}.php` |
| Stored product (`products/getOne`) | Moloni item update merges the stored product | `tests/Unit/Item/Update/ProviderData/{PayloadTest,TaxTest}.php` |
| Country, language, document set, OAuth grant | Moloni client and invoice create, every Moloni request | every Moloni unit test, through `mockConfiguration(Provider::MOLONI)` |

Some tests assert the lookup itself: "fetches the provider payment methods once per invoice", "fetches the provider due dates in a single page", "does not fetch the provider due dates when no due date is set", "only sends payments on document types that accept them", and the "fails … without any request" tests that use `Http::assertNothingSent`.

**Open questions:**
1. Make the lookups replaceable (e.g. inject or set the resolved values) so unit tests never reach HTTP, or allow lookup fakes in unit tests as a stated exception in `.claude/rules/testing.md`?
   **Default:** allow lookup fakes in unit tests as a stated exception in `.claude/rules/testing.md`.
2. Where do the lookup-count assertions ("once per invoice", "single page", "no request") belong after the change?
   **Default:** they stay in `tests/Unit/`, under the lookup exception.

### 5. Provider class structure drift

**Status:** Todo

**Problem:** classes that break `.claude/rules/provider-operations.md` beyond what entry 1 covers.

- `src/Provider/{Moloni,CegidVendus}/Client/Update.php` extend `Client/Create.php`, so an `Update` is also a `ShouldCreateClient`. Proposed fix: move the shared `build*()` methods into a per-provider trait.
- Moloni `Client::update` reuses `Create::getPayload()`, which sends creation defaults (country PT, language pt, 15-day maturity, discount, credit limit, delivery method 0). That overwrites stored values. Proposed fix: read the stored client with `customers/getOne` and merge it, as Moloni `Item\Update` does. This changes behaviour; docs currently describe a full replace.
- Contracts that extend `ShouldExecute` inherit `execute(): self` bound to `ShouldExecute`, so the editor flags calls like `getClient()` after `execute()` (e.g. `tests/Feature/Client/UpdateTest.php`). Six contracts already redeclare `execute(): self` (commit `5d98059`). Proposed fix: redeclare it on the rest.

**Open questions:**
1. Apply each proposed fix above? Answer per bullet.
   **Default:** first bullet (shared trait for `Update`/`Create`): no. Third bullet (redeclare `execute(): self`): yes.
   Second bullet: the package must not guess default values. Remove the Moloni client defaults from both create and update. The only place the package guesses values is issuing an invoice. No merge with the stored client.

## Test naming

- `tests/Unit/Client/Update/ProviderData/PayloadTest.php` "does not send the moloni number when the client has no reference" checks both providers (no `number` on Moloni, no `external_reference` on Vendus). Proposed name: "does not send the reference when the client has none", with a `match ($provider)` picking the key.
- `tests/Unit/Item/Delete/PayloadTest.php` "sends the item id in the payload" asserts an empty payload on Cegid Vendus. Proposed name: "builds the delete payload for the item id".
