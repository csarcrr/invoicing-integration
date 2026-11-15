# Test Analyses

This report inventories every note left in `tests/`: 13 notes in total. 11 are Pest `->note()` calls chained onto a test (10 start with `todo:`, 1 with `verify:`), 1 is a `//` line comment and 1 is a docblock. By file: `tests/Feature/Invoice/PdfTest.php` has 4, `tests/Feature/Invoice/GetTest.php` has 2, and each of the other seven files has 1 (`tests/Unit/Invoice/Cancel/PayloadTest.php`, `tests/Feature/Warehouse/CreateTest.php`, `tests/Feature/Client/DeleteTest.php`, `tests/Feature/Invoice/FindTest.php`, `tests/Feature/Invoice/CancelTest.php`, `tests/Feature/Item/FindTest.php`, `tests/Feature/Providers/Moloni/AuthenticationTest.php`). No `skip`, `todo()`, `markTestSkipped`, `markTestIncomplete`, `FIXME`, `HACK` or `XXX` markers exist in `tests/`. The JSON fixtures contain no note keys. Note text longer than 300 characters is trimmed and marked with "…".

## Inventory

### tests/Unit/Invoice/Cancel/PayloadTest.php

| ID | Line | Enclosing test | Kind | Note text | Resolved |
| --- | --- | --- | --- | --- | --- |
| F1 | 27 | sends the notes as observation on moloni only | Pest `->note()` (todo) | todo: this cannot exist in the package. if the feature/field is only available in a specific provider, it should not be supported. this will steer users to believe it is supported in all providers and might result in unexected error or behaviours down the line. a package review must be done… | yes |

### tests/Feature/Warehouse/CreateTest.php

| ID | Line | Enclosing test | Kind | Note text | Resolved |
| --- | --- | --- | --- | --- | --- |
| F2 | 59 | fails with the provider error when the Moloni warehouse is not created | Pest `->note()` (todo) | todo: again this problem, same types of requests and different tests that can be joined by having multiple http fakes... | yes |

### tests/Feature/Client/DeleteTest.php

| ID | Line | Enclosing test | Kind | Note text | Resolved |
| --- | --- | --- | --- | --- | --- |
| F3 | 30 | moloni fails with the provider message when the client is not deleted | Pest `->note()` (todo) | todo: this problem persists... same exception different messages, just mash them into the same test and ignore the message. | yes |

### tests/Feature/Invoice/FindTest.php

| ID | Line | Enclosing test | Kind | Note text | Resolved |
| --- | --- | --- | --- | --- | --- |
| F4 | 63 | returns an empty list when cegid vendus has no invoices | Pest `->note()` (todo) | todo: discover the reason for this to be a vendus specific test only | yes |

### tests/Feature/Invoice/GetTest.php

| ID | Line | Enclosing test | Kind | Note text | Resolved |
| --- | --- | --- | --- | --- | --- |
| F5 | 38 | cegid vendus maps the atcud to atcudHash and moloni leaves it null | Pest `->note()` (todo) | todo: verify if moloni has an equivalent to atcud | no |
| F6 | 79 | an unknown document type falls back to FT | Pest `->note()` (verify) | verify: we cannot have unsupported document types | no |

### tests/Feature/Invoice/CancelTest.php

| ID | Line | Enclosing test | Kind | Note text | Resolved |
| --- | --- | --- | --- | --- | --- |
| F7 | 30 | moloni fails with the provider message when the document is not cancelled | Pest `->note()` (todo) | todo: this and "cegid vendus fails with the provider error when the request is rejected", are very similar in behaviour. the only thing that differs is the message. maybe unify the tests and do not look at the message type, but only to the exception type | yes |

### tests/Feature/Invoice/PdfTest.php

| ID | Line | Enclosing test | Kind | Note text | Resolved |
| --- | --- | --- | --- | --- | --- |
| F8 | 44 | the output url is the moloni pdf link and null on cegid vendus | Pest `->note()` (todo) | todo: change test namings like these. we can have multiple new providers. this needs to be something more generic like "outputs the expected pdf content or pdf url" | no |
| F9 | 58 | moloni keeps the pdf link when the download does not return a pdf | Pest `->note()` (todo) | todo: naming keeps being provider specific and it cannot. example fix: "providers that don\'t directly support download, are always able to fallback to get the url" | no |
| F10 | 74 | requests the moloni download with the token from the pdf link and the document id | Pest `->note()` (todo) | todo: this is a provider specific test | no |
| F11 | 84 | fails when moloni returns an empty pdf link | Pest `->note()` (todo) | todo: this is a provider specific test | no |

### tests/Feature/Item/FindTest.php

| ID | Line | Enclosing test | Kind | Note text | Resolved |
| --- | --- | --- | --- | --- | --- |
| F12 | 115 | file scope (above "fails when attempting to go above the total pages") | line comment `//` | Moloni does not report its total pages, so there is no known upper limit to exceed. | no |

### tests/Feature/Providers/Moloni/AuthenticationTest.php

| ID | Line | Enclosing test | Kind | Note text | Resolved |
| --- | --- | --- | --- | --- | --- |
| F13 | 39-41 (text on 40) | file scope (above "caches the token after a successful fetch") | docblock (prose) | access_token is solved by Pest.php -> mockConfiguration | no |

## Patterns

- Mechanism: 11 of the 13 notes are Pest `->note()` calls. They are not comments, but they are prose attached to test code, so they meet the same intent as a comment. The 2 remaining notes (`Item/FindTest.php:115` and `AuthenticationTest.php:39-41`) are a comment and a prose docblock, which AGENTS.md section 8 and the global rule ("no prose docblocks, even when the surrounding code has them") forbid. Whether `->note()` counts as a prohibited comment is an open question.
- Recurring theme: "provider specific" appears in 4 notes (`Invoice/Cancel/PayloadTest.php:27`, `Invoice/PdfTest.php:58`, `:74`, `:84`), and 2 more point at a provider-only test (`Invoice/FindTest.php:63`, `Item/FindTest.php:115`).
- Duplicated notes: the same instruction ("same exception, different messages, merge the tests") appears in `Warehouse/CreateTest.php:59`, `Client/DeleteTest.php:30` and `Invoice/CancelTest.php:30`. `Warehouse/CreateTest.php:59` refers back to the same problem ("again this problem"). `Invoice/PdfTest.php:74` and `:84` are identical text ("todo: this is a provider specific test").
- Files with the most notes: `tests/Feature/Invoice/PdfTest.php` (4) and `tests/Feature/Invoice/GetTest.php` (2). All other files have 1.
- Conflicts with AGENTS.md: the two prose notes (`Item/FindTest.php:115`, `AuthenticationTest.php:39-41`) break the no-comment rule. `Invoice/Cancel/PayloadTest.php:27` argues against the "Unsupported fields" rule in `.agents/rules/provider-operations.md` (leave the field out, document it).
- Conflicts between notes and code or docs: `Invoice/GetTest.php:38` asks whether Moloni has an ATCUD. `docs/invoices/getting-an-invoice.md:35` says Moloni does not return one, and `providers-docs/moloni/dev/documents/documents/getbymbreference/index.html:920` shows the field. The Moloni-only observation in `Invoice/Cancel/PayloadTest.php:27` also conflicts with the Moloni-only design already documented in `docs/invoices/cancelling-an-invoice.md`.
- Conflicts between notes: `Invoice/Cancel/PayloadTest.php:27` says provider-only features should not exist, while `Invoice/GetTest.php:38` and `Invoice/PdfTest.php:58` both ask for provider-specific mappings or fallbacks. The notes do not agree on whether provider-specific behaviour is acceptable.
- Inconsistent tags: `todo:` (10 notes), `verify:` (`GetTest.php:79`), and an `example fix:` inside `PdfTest.php:58`.

## Open questions

1. `Invoice/Cancel/PayloadTest.php:27`: should the Moloni-only cancellation `observation` stay, as the docs describe, or be removed as the note asks? AGENTS.md section 7 and `.agents/rules/provider-operations.md` point towards keeping it and documenting it.
2. `Invoice/GetTest.php:38`: does a Moloni endpoint return `atcud`? The docs say no. The Moloni docs schema (`getbymbreference/index.html:920`) lists it. Which endpoint is meant, and should `atcudHash` be mapped for Moloni?
3. `Invoice/GetTest.php:79`: can a provider return a document type outside the `InvoiceType` enum? If not, the silent `FT` fallback can stay; if so, should it still fall back silently?
4. Is Pest's `->note()` metadata meant to stay in the test suite? It is not a comment, but it works as one. Unverified: whether Pest renders the note anywhere (the method is defined at `vendor/pestphp/pest/src/Concerns/Testable.php:137`, and its rendering was not checked).
5. `Warehouse/CreateTest.php:59`, `Client/DeleteTest.php:30` and `Invoice/CancelTest.php:30`: should these be merged into `providers` dataset tests that assert only the exception class, as `testing.md` describes? The message asserted today differs by provider (`A001 - No data` for Vendus and provider text for Moloni).
6. `Invoice/PdfTest.php:58`: should the "fall back to the URL when no download is possible" rule apply to every provider? Cegid Vendus returns no URL today.
7. `Item/FindTest.php:115`: is "Moloni does not report its total pages" documented in `docs/items/finding-an-item.md`? It is documented for invoices (`docs/invoices/finding-invoices.md:128`) but was not found for items.

## Excluded hits

Keyword and comment searches (`//`, `/*`, `#`, `todo`, `skip`, `TODO`, `FIXME`, `NOTE`, `markTest`, `note`, `fix`, `remove`, `verify`, `reason`, `comment`) produced the hits below. Each is listed with the reason it is not a note. 40 line-level hits are listed; 15 fixture JSON files are listed as one group; the `fixtures()` helper matches (the `fix` keyword) and `'remove'` or `'Broken …'` data strings are code or data and are not enumerated.

| Count | File and line(s) | Reason |
| --- | --- | --- |
| 1 | tests/Unit/PaymentMethod/Create/PayloadTest.php:24 | Code line matched the broad keyword search. It contains no note text. |
| 2 | tests/Feature/Invoice/OutputTest.php:100, 111 | Test data strings (`'//double/slash.pdf'`, `'Invoice #123'`). A `#` and `//` inside strings. |
| 7 | tests/Feature/HttpRequestTest.php:11, 19, 33, 50, 72, 88, 96 | URL `'http://provider.site/example'`. |
| 2 | tests/Feature/Invoice/PdfTest.php:42, 57 | URL `https://www.moloni.pt/downloads/?h=…` in an assertion. |
| 1 | tests/Pest.php:82 | Fake-HTTP glob key `'api.moloni.pt/v1/grant/*'` (contains `/*` but is not a comment). |
| 1 | tests/Feature/Providers/Moloni/AuthenticationTest.php:53 | Same glob key, inside a test body. |
| 26 | Data or test-name lines with the word `note`/`notes`: tests/Unit/ItemStock/Move/PayloadTest.php:16, 55, 65, 66, 67; tests/Unit/ItemStock/DataTest.php:27; tests/Unit/Traits/HidesAdditionalDataTest.php:29, 30, 32, 33; tests/Unit/Item/Update/ProviderData/PayloadTest.php:123, 129; tests/Unit/Client/Update/ProviderData/PayloadTest.php:27, 44, 57; tests/Unit/Client/Find/PayloadTest.php:30, 35; tests/Unit/Client/Create/ProviderData/PayloadTest.php:26; tests/Unit/Invoice/Cancel/PayloadTest.php:20, 21, 29; tests/Unit/Invoice/Create/ProviderData/ItemTest.php:22; tests/Unit/Invoice/Create/ProviderData/NotesTest.php:10, 14; tests/Unit/Invoice/Create/ProviderData/ClientTest.php:56, 84 | Field names (`notes`, `note`) and test data, or test names such as "sends the notes as observation". Not notes. |
| 15 files | tests/Fixtures/IntegrationProvider/{Moloni,CegidVendus}/… (15 JSON files, including `Moloni/Response/Client/response_multiple.json` and `CegidVendus/Request/Invoice/notes.json`) | Domain values under `notes` or `field_notes` keys. The keys hold business data, not annotations. |

The `->note()` calls, the `//` comment and the docblock are not excluded; they are in the Inventory.

## Notes on method

- Grep searches were run across `tests/` (excluding `tests/Fixtures` for the first pass and separately over the JSON fixtures). Each hit was read in context.
- Every inventory line number was checked by reading the file.
- Provider and `src/` behaviour cited above was read in `src/Provider/…`, `src/Traits/…` and `tests/Pest.php`. Items marked "unverified" were not read.
