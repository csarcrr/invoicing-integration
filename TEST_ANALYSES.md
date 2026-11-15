# Test Analyses

This report inventories every note left in `tests/`: 13 notes in total. 11 are Pest `->note()` calls chained onto a test (10 start with `todo:`, 1 with `verify:`), 1 is a `//` line comment and 1 is a docblock. By file: `tests/Feature/Invoice/PdfTest.php` has 4, `tests/Feature/Invoice/GetTest.php` has 2, and each of the other seven files has 1 (`tests/Unit/Invoice/Cancel/PayloadTest.php`, `tests/Feature/Warehouse/CreateTest.php`, `tests/Feature/Client/DeleteTest.php`, `tests/Feature/Invoice/FindTest.php`, `tests/Feature/Invoice/CancelTest.php`, `tests/Feature/Item/FindTest.php`, `tests/Feature/Providers/Moloni/AuthenticationTest.php`). No `skip`, `todo()`, `markTestSkipped`, `markTestIncomplete`, `FIXME`, `HACK` or `XXX` markers exist in `tests/`. The JSON fixtures contain no note keys. Note text longer than 300 characters is trimmed and marked with "…".

## Inventory

### tests/Unit/Invoice/Cancel/PayloadTest.php

| Line | Enclosing test | Kind | Note text |
| --- | --- | --- | --- |
| 27 | sends the notes as observation on moloni only | Pest `->note()` (todo) | todo: this cannot exist in the package. if the feature/field is only available in a specific provider, it should not be supported. this will steer users to believe it is supported in all providers and might result in unexected error or behaviours down the line. a package review must be done… |

### tests/Feature/Warehouse/CreateTest.php

| Line | Enclosing test | Kind | Note text |
| --- | --- | --- | --- |
| 59 | fails with the provider error when the Moloni warehouse is not created | Pest `->note()` (todo) | todo: again this problem, same types of requests and different tests that can be joined by having multiple http fakes... |

### tests/Feature/Client/DeleteTest.php

| Line | Enclosing test | Kind | Note text |
| --- | --- | --- | --- |
| 30 | moloni fails with the provider message when the client is not deleted | Pest `->note()` (todo) | todo: this problem persists... same exception different messages, just mash them into the same test and ignore the message. |

### tests/Feature/Invoice/FindTest.php

| Line | Enclosing test | Kind | Note text |
| --- | --- | --- | --- |
| 63 | returns an empty list when cegid vendus has no invoices | Pest `->note()` (todo) | todo: discover the reason for this to be a vendus specific test only |

### tests/Feature/Invoice/GetTest.php

| Line | Enclosing test | Kind | Note text |
| --- | --- | --- | --- |
| 38 | cegid vendus maps the atcud to atcudHash and moloni leaves it null | Pest `->note()` (todo) | todo: verify if moloni has an equivalent to atcud |
| 79 | an unknown document type falls back to FT | Pest `->note()` (verify) | verify: we cannot have unsupported document types |

### tests/Feature/Invoice/CancelTest.php

| Line | Enclosing test | Kind | Note text |
| --- | --- | --- | --- |
| 30 | moloni fails with the provider message when the document is not cancelled | Pest `->note()` (todo) | todo: this and "cegid vendus fails with the provider error when the request is rejected", are very similar in behaviour. the only thing that differs is the message. maybe unify the tests and do not look at the message type, but only to the exception type |

### tests/Feature/Invoice/PdfTest.php

| Line | Enclosing test | Kind | Note text |
| --- | --- | --- | --- |
| 44 | the output url is the moloni pdf link and null on cegid vendus | Pest `->note()` (todo) | todo: change test namings like these. we can have multiple new providers. this needs to be something more generic like "outputs the expected pdf content or pdf url" |
| 58 | moloni keeps the pdf link when the download does not return a pdf | Pest `->note()` (todo) | todo: naming keeps being provider specific and it cannot. example fix: "providers that don\'t directly support download, are always able to fallback to get the url" |
| 74 | requests the moloni download with the token from the pdf link and the document id | Pest `->note()` (todo) | todo: this is a provider specific test |
| 84 | fails when moloni returns an empty pdf link | Pest `->note()` (todo) | todo: this is a provider specific test |

### tests/Feature/Item/FindTest.php

| Line | Enclosing test | Kind | Note text |
| --- | --- | --- | --- |
| 115 | file scope (above "fails when attempting to go above the total pages") | line comment `//` | Moloni does not report its total pages, so there is no known upper limit to exceed. |

### tests/Feature/Providers/Moloni/AuthenticationTest.php

| Line | Enclosing test | Kind | Note text |
| --- | --- | --- | --- |
| 39-41 (text on 40) | file scope (above "caches the token after a successful fetch") | docblock (prose) | access_token is solved by Pest.php -> mockConfiguration |

## Analysis

### tests/Unit/Invoice/Cancel/PayloadTest.php:27

- Category: other (design decision, TODO).
- Plain words: a provider-only feature (the Moloni cancellation `observation`) should not exist in the package. The note asks for a package review that keeps only the features all providers share, and adds that the test "omits the observation when no notes are set" fits the same category.
- Accuracy: the first part matches the code. Moloni sends the observation at `src/Provider/Moloni/Invoice/Cancel.php:69-71`. The Cegid Vendus cancel path maps no `observation` (a grep of `src/Provider/CegidVendus/Invoice` finds none). `docs/invoices/cancelling-an-invoice.md:16` and `:26` document it as Moloni-only. The recommendation conflicts with `.agents/rules/provider-operations.md` ("Unsupported fields": leave the field out and document it in the provider's section) and with AGENTS.md section 7 ("Provider rules belong to the provider"). The note's premise is therefore a decision, not a fact (see Open questions 1).
- Where it belongs instead: the Moloni-only behaviour is already in `docs/invoices/cancelling-an-invoice.md`. The decision to keep or drop the field is for a human. The note should not remain in the test.

### tests/Feature/Warehouse/CreateTest.php:59

- Category: TODO (missing consolidation).
- Plain words: the Moloni rejection test repeats the pattern of other tests, and the note suggests joining them with several HTTP fakes.
- Accuracy: the Moloni test (lines 53-59) and the Cegid Vendus test (lines 61-67) both expect `RequestFailedException`. `.agents/rules/testing.md` (rule on "When every provider throws the same exception") says to write one `providers` dataset test that asserts only the exception class. That rule answers the note's question; the "multiple http fakes" idea is not needed for that.
- Where it belongs instead: a `providers` dataset test in `tests/Feature/Warehouse/CreateTest.php` (a code change, not a note). Observation only.

### tests/Feature/Client/DeleteTest.php:30

- Category: TODO (missing consolidation).
- Plain words: both providers throw the same exception with different messages; the note says to merge the tests and ignore the message.
- Accuracy: accurate. Line 30 asserts the Moloni message `Client is referenced by a document`. Lines 32-38 assert the Vendus message `A001 - No data`. `.agents/rules/testing.md` says to assert only the exception class in this case.
- Where it belongs instead: the test itself (a `providers` dataset test with a class-only assertion). Observation only.

### tests/Feature/Invoice/FindTest.php:63

- Category: provider quirk (TODO).
- Plain words: the test should be Vendus-only, and the author does not know why.
- Accuracy: the reason is in `src`. `src/Provider/CegidVendus/Invoice/Find.php:46` calls `hasNoData()` from `src/Traits/DetectsNoData.php:13-17`, which treats a 404 with code `A001` as an empty list. The Moloni invoice find was not read in full (unverified whether it has the same path). The test already mocks `Provider::CEGID_VENDUS` at line 58 and its name says "cegid vendus".
- Where it belongs instead: a provider note in `docs/invoices/finding-invoices.md`, if the behaviour is worth documenting. The test name already states the scope. Observation only.

### tests/Feature/Invoice/GetTest.php:38

- Category: TODO (missing mapping, possibly outdated docs).
- Plain words: the author wants to confirm whether Moloni has an ATCUD equivalent to map to `atcudHash`.
- Accuracy: the code and docs disagree with the Moloni docs. `src/Provider/CegidVendus/Invoice.php:25` maps `atcudHash` from `atcud`. `src/Provider/Moloni/Invoice.php:18-28` has no ATCUD mapping. `docs/invoices/getting-an-invoice.md:35` says `atcudHash` is `null` "because Moloni does not return the ATCUD", and `docs/invoices/creating-an-invoice.md:369` says "The ATCUD is not returned". However, `providers-docs/moloni/dev/documents/documents/getbymbreference/index.html:920` lists `"atcud": string` in the document schema. Whether the `get` endpoint returns it is unverified.
- Where it belongs instead: if the field is mapped, the mapping decision goes into code (AGENTS.md "Map, do not manage") and the `getting-an-invoice.md` note is updated. Until then, the question is open (Open questions 2).

### tests/Feature/Invoice/GetTest.php:79

- Category: known limitation (unverified claim).
- Plain words: the author says unsupported document types cannot exist (a `verify:` marker).
- Accuracy: the fallback is implemented in both providers: `src/Provider/Moloni/Invoice.php:27` and `src/Provider/CegidVendus/Invoice.php:26` use `InvoiceType::tryFrom(...) ?? InvoiceType::Invoice`. `docs/invoices/finding-invoices.md:139` documents the `FT` fallback for lists. Whether a provider can return other document types was not checked against `providers-docs` (unverified).
- Where it belongs instead: `docs/invoices/getting-an-invoice.md` (a fallback note) if the behaviour is confirmed. The test name already describes the fallback.

### tests/Feature/Invoice/CancelTest.php:30

- Category: TODO (missing consolidation).
- Plain words: the Moloni cancel-rejection test and the Cegid Vendus rejection test are alike except for the message; the note suggests asserting only the exception type.
- Accuracy: the Moloni message comes from the provider response (`src/Provider/Moloni/Invoice/Cancel.php:43-47`). The Vendus test (lines 32-38) asserts `A001 - No data`. `.agents/rules/testing.md` supports asserting only the class.
- Where it belongs instead: a `providers` dataset test in the same file (code change). Observation only.

### tests/Feature/Invoice/PdfTest.php:44

- Category: TODO (naming).
- Plain words: the test name is provider-specific; the author suggests a generic name such as "outputs the expected pdf content or pdf url".
- Accuracy: the test asserts only the `url` (lines 40-43). The suggested name describes a content assertion that the test does not make, so the suggestion does not match the body.
- Where it belongs instead: the test name only. Observation only.

### tests/Feature/Invoice/PdfTest.php:58

- Category: TODO (design / naming).
- Plain words: the test name is Moloni-specific. The author wants a generic rule: providers without a direct download fall back to returning the URL.
- Accuracy: the Moloni behaviour already exists. `src/Provider/Moloni/Invoice/Pdf.php:98-99` returns `null` content when the download fails or is not a PDF, and `:57` keeps the URL. Cegid Vendus returns no URL (`tests/Feature/Invoice/PdfTest.php:41`), so the generic rule is not implemented for every provider.
- Where it belongs instead: the test name, and a package-wide statement in the output docs only if the rule is adopted (the output docs page was not read; unverified). The note contains an escaped apostrophe (`don\'t`) exactly as written in the file.

### tests/Feature/Invoice/PdfTest.php:74

- Category: explanation of intent (provider-specific test marker).
- Plain words: the test is specific to Moloni.
- Accuracy: accurate. The test uses `mockConfiguration(Provider::MOLONI)` at line 61 and asserts the Moloni download query (`action`, `h`, `d`) built at `src/Provider/Moloni/Invoice/Pdf.php:92-96`.
- Where it belongs instead: `tests/Unit/Providers/Moloni/` holds provider internals with no counterpart (`.agents/rules/testing.md`). Observation only.

### tests/Feature/Invoice/PdfTest.php:84

- Category: explanation of intent (provider-specific test marker).
- Plain words: the test is specific to Moloni.
- Accuracy: accurate. The test mocks Moloni (line 77). The exception message `Invoice PDF link not available.` matches `src/Provider/Moloni/Invoice/Pdf.php:52`.
- Where it belongs instead: the same place as line 74. Observation only.

### tests/Feature/Item/FindTest.php:115

- Category: explanation of intent (provider quirk), written as a line comment.
- Plain words: Moloni reports no total page count, so there is no upper page limit to exceed. The test that follows covers the upper limit on Cegid Vendus only.
- Accuracy: accurate for the code. `src/Provider/Moloni/Item/Find.php` never calls `totalPages()`, so the default `null` from `src/Traits/HasPaginator.php:18` applies, and `page()` only throws above the limit when it is not `null` (`HasPaginator.php:62`). `docs/invoices/finding-invoices.md:128` documents the Moloni `null`. The items docs (`docs/items/finding-an-item.md`) were searched for the same fact and do not state it (unverified against `providers-docs`).
- Where it belongs instead: a Moloni note in `docs/items/finding-an-item.md`, and the upper-limit scope is already in the test name. AGENTS.md section 8 bans prose comments, so the line should be removed from the test.

### tests/Feature/Providers/Moloni/AuthenticationTest.php:39-41

- Category: outdated (prose docblock, partly misleading).
- Plain words: the `access_token` value comes from `Pest.php` through `mockConfiguration`, and the next test relies on that.
- Accuracy: partly true. `tests/Pest.php:81-92` fakes the grant endpoint returning `fresh-access-token`. `tests/Pest.php:94-96` seeds the cache. But `tests/Feature/Providers/Moloni/AuthenticationTest.php:14` calls `Cache::flush()` in `beforeEach`, after `mockConfiguration` at line 13, which clears that seeded cache. The test below (lines 42-48) therefore does not rely on the Pest.php cache seed, and the note sits above the wrong test.
- Where it belongs instead: nowhere; it can be deleted. The HTTP fake is already visible in `tests/Pest.php:81-92`. Observation only.

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
