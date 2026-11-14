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
| 1 | Request-shape tests use `assertSent` | Done | Class changes + test rewrites |
| 2 | Response-handling tests live in `tests/Unit/` | Done | Move to `tests/Feature/` |
| 3 | HTTP layer tests live in `tests/Unit/` | Done | Move to `tests/Feature/` |
| 4 | Payload building triggers HTTP lookups | Blocked (open questions) | Temporary exception; approach later |
| 5 | Provider class structure drift | Done | Not test-only |
| 6 | Test naming | Done | Two test renames |

## Entries

### 1. Request-shape tests use `assertSent`

**Status:** Done

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
- [x] User ran the test suite and checks

**Notes:** committed in `e9645ca` on 2026-11-14, tests pass.
- Classes: `src/Provider/{Moloni,CegidVendus}/Client/{Get,Delete}.php`, `src/Provider/{Moloni,CegidVendus}/Item/Get.php`, `src/Provider/{Moloni,CegidVendus}/Invoice/{Get,Pdf,Cancel,Find}.php`, `src/Provider/Moloni/{Country/Get,Language/Get,DocumentSeries/Find}.php`.
- Contracts: `ShouldGetClient`, `ShouldDeleteClient`, `ShouldGetItem`, `ShouldGetInvoice`, `ShouldGetInvoicePdf`, `ShouldCancelInvoice` gained `getUrl()` and `ShouldHavePayload`. `ShouldFindInvoice` gained `getUrl()`.
- Tests: the 10 files in the table, `tests/Feature/Invoice/PdfTest.php` (moved Moloni download test), and `tests/Unit/Client/StructureTest.php` (gets an id, because the id check now runs in the constructor).
- Docs: `docs/api-reference.md`, `docs/clients/deleting-a-client.md`, `docs/invoices/getting-an-invoice.md`, `docs/invoices/cancelling-an-invoice.md`. The "ID is required" error now comes from the facade call, not `execute()`.
- Removed: `buildId()` on Moloni Client Get and Item Get (renamed to `buildPayload()`), and `endpoint()` on Moloni Invoice Find (merged into `buildUrl()`).
- Kept: Moloni `Item\Get::execute()` still sends through the shared `Moloni\Item::getProduct()`, which writes `products/getOne` directly. Moloni `Item\Update` uses it too.
- Beyond the table: `DocumentSeries\Find` also builds its payload (`qty`, `offset`). Both `Invoice\Find` classes build `$url`, because the Moloni test asserts the endpoint.

### 2. Response-handling tests live in `tests/Unit/`

**Status:** Done

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
- [x] User ran the test suite and checks

**Notes:** committed in `2288f58` on 2026-11-14, tests pass.
- Moved into: `tests/Feature/Client/GetTest.php`, `tests/Feature/Invoice/GetTest.php`, `tests/Feature/Invoice/PdfTest.php`, and new `tests/Feature/Providers/Moloni/{Country/GetTest,Language/GetTest,DocumentSeries/FindTest}.php`. The new Moloni files carry the source `beforeEach` (`Cache::flush()`, `mockConfiguration(Provider::MOLONI)`).
- Moved out of: `tests/Unit/Client/Get/IdTest.php`, `tests/Unit/Invoice/Get/IdTest.php`, `tests/Unit/Invoice/Pdf/IdTest.php`, `tests/Unit/Providers/Moloni/{Country/GetTest,Language/GetTest,DocumentSeries/FindTest}.php`. Imports made unused were removed.
- The moved PDF test uses `test(...)` to match `tests/Feature/Invoice/PdfTest.php`.
- `tests/Feature/Providers/` gets no Pest group (user decision).

### 3. HTTP layer tests live in `tests/Unit/`

**Status:** Done

**Problem:** these files test the HTTP layer itself, so faking is the subject of the test.

- `tests/Unit/HttpRequestTest.php`: status codes and error payloads mapped to exceptions.
- `tests/Unit/Providers/RequestAuthenticationTest.php`: request authentication per provider.
- `tests/Unit/Providers/Moloni/AuthenticationTest.php`: OAuth token fetch, URL and caching.
- `tests/Unit/Providers/Moloni/RequestTest.php`: "asks for human readable errors".

**Open questions:**
1. Move these files to `tests/Feature/`, or keep them in `tests/Unit/` and state the exception in `.claude/rules/testing.md`?
   **Default:** move them to `tests/Feature/`. No rule exception.

**Done when:**
- [x] The four files live under `tests/Feature/` (Moloni-only ones in `tests/Feature/Providers/Moloni/`) with unchanged test bodies
- [x] No file is left in `tests/Unit/` for them
- [x] User ran the test suite and checks

**Notes:** code written 2026-11-14, tests pass.
- Moved with `git mv`, bodies unchanged: `tests/Feature/HttpRequestTest.php`, `tests/Feature/Providers/RequestAuthenticationTest.php`, `tests/Feature/Providers/Moloni/AuthenticationTest.php`, `tests/Feature/Providers/Moloni/RequestTest.php`.
- `tests/Pest.php:53`: `ProviderTestingGroup` now names the four moved files, so they keep their group. The entry 2 files in `tests/Feature/Providers/` stay without a group.

### 4. Payload building triggers HTTP lookups

**Status:** Blocked (open questions)

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
   **Default:** lookup fakes in unit tests are a temporary exception, not a rule. Do not write them into `.claude/rules/testing.md`. A different approach will replace them later.
2. Where do the lookup-count assertions ("once per invoice", "single page", "no request") belong after the change?
   **Default:** they stay in `tests/Unit/` for now, under the temporary exception.
3. Which approach replaces the lookup fakes? To be decided later by the user.

**Interaction with entry 5:** removing the Moloni client defaults (entry 5, second bullet) removes the 15-day maturity default, so client create and update no longer look up due dates when `defaultPayDue` is not set. Update the due-date row above after entry 5.

**Done when:**
- [ ] The user picked the approach (question 3)
- [ ] No unit test fakes a lookup

**Notes:** a rule bullet was added to `.claude/rules/testing.md` on 2026-11-14 and removed the same day at the user's request.

### 5. Provider class structure drift

**Status:** Done

**Problem:** classes that break `.claude/rules/provider-operations.md` beyond what entry 1 covers.

- `src/Provider/{Moloni,CegidVendus}/Client/Update.php` extend `Client/Create.php`, so an `Update` is also a `ShouldCreateClient`. Proposed fix: move the shared `build*()` methods into a per-provider trait. **Won't fix** (user decision).
- Moloni `Client::update` reuses `Create::getPayload()`, which sends creation defaults (country PT, language pt, 15-day maturity, discount, credit limit, delivery method 0). That overwrites stored values. Fix: remove the defaults from create and update (see the Default below). This changes behaviour; update the client docs.
- Contracts that extend `ShouldExecute` inherit `execute(): self` bound to `ShouldExecute`, so the editor flags calls like `getClient()` after `execute()` (e.g. `tests/Feature/Client/UpdateTest.php`). Six contracts already redeclare `execute(): self` (commit `5d98059`). Proposed fix: redeclare it on the rest.

**Open questions:**
1. Apply each proposed fix above? Answer per bullet.
   **Default:** first bullet (shared trait for `Update`/`Create`): no. Third bullet (redeclare `execute(): self`): yes.
   Second bullet: the package must not guess default values. Remove the Moloni client defaults from both create and update. The only place the package guesses values is issuing an invoice. No merge with the stored client.
2. Moloni `customers/insert` requires `payment_method_id` (`providers-docs/moloni/dev/entities/customers/insert/index.html`), and the package sends `0`. `ClientData` has no field for it, so after removing the default no input can produce it. Same for `payment_day` and `salesman_id`, which are optional on Moloni. Add `ClientData` fields for them, drop them from the payload, or keep `payment_method_id => 0` as the one exception?
   **Default:** add `ClientData` fields for them.

**Done when:**
- [x] Moloni client create and update send no guessed values (country PT, language pt, 15-day maturity, discount, credit limit, delivery method 0), subject to question 2
- [x] Every contract that extends `ShouldExecute` redeclares `execute(): self`
- [x] Client docs describe what is sent when a field is not set
- [x] User ran the test suite and checks

**Notes:** code written 2026-11-14, tests pass.
- `src/Provider/Moloni/Client/Create.php`: country, language and due date are looked up and sent only when set; `discount`, `credit_limit`, `delivery_method_id`, `payment_method_id`, `payment_day`, `salesman_id` are sent only when set. Removed `buildPendingIds()`, the `DueDateTerm` import and the `?? 0` on the due date. Added `buildPaymentMethod()`, `buildSalesman()`.
- `src/Data/ClientData.php`: new `paymentMethodId`, `paymentDay`, `salesmanId` (`integer|min:0`). Moloni only; Cegid Vendus does not send them.
- 16 contracts under `src/Contracts/IntegrationProvider/` redeclare `execute(): self` (form from commit `5d98059`).
- Tests: `tests/Unit/Client/Create/ProviderData/{CommercialTermsTest,CountryTest,LanguageTest,DefaultPayDueTest}.php` assert the fields are absent when unset; new `tests/Unit/Client/Create/ProviderData/PaymentTest.php`; new test in `tests/Unit/Client/Update/ProviderData/PayloadTest.php`. Test names no longer say "moloni" when they run on both providers.
- Feature edits: `tests/Feature/Client/CreateTest.php` expects 1 request on Moloni (was 4: the lookups no longer run); `tests/Fixtures/IntegrationProvider/Moloni/Request/Client/client_full.json` lost the default values.
- Docs: `docs/clients/creating-a-client.md` (new fields, no defaults, Moloni required-field note from `customers/insert`), `docs/clients/updating-a-client.md`, `docs/api-reference.md`, `docs/changelog.md`.

### 6. Test naming

**Status:** Done

- `tests/Unit/Client/Update/ProviderData/PayloadTest.php` "does not send the moloni number when the client has no reference" checks both providers (no `number` on Moloni, no `external_reference` on Vendus). Proposed name: "does not send the reference when the client has none", with a `match ($provider)` picking the key.
- `tests/Unit/Item/Delete/PayloadTest.php` "sends the item id in the payload" asserts an empty payload on Cegid Vendus. Proposed name: "builds the delete payload for the item id".

**Open questions:**
1. Apply both renames?
   **Default:** yes, apply both.

**Done when:**
- [x] Both tests carry the approved names
- [x] User ran the test suite and checks

**Notes:** applied 2026-11-14, tests pass, in `tests/Unit/Client/Update/ProviderData/PayloadTest.php` (with the `match ($provider)` key and its review note removed) and `tests/Unit/Item/Delete/PayloadTest.php`.
