# Feature Backlog

Operations that both providers (Moloni and Cegid Vendus) support but the package does not expose yet. Two exceptions: entry 10 (`Warehouse::create`) is implemented on Moloni only, because Cegid Vendus creates stores only in API v1.2; entry 13 migrates the Vendus client to v1.2. Domain terms and their provider names are in `GLOSSARY.md`.

## How to use this file

- Pick the first entry on the Status board whose status is `Todo`, following the order below.
- Before picking it, check `src/Actions/` and `src/Provider/*` to confirm the operation is not already implemented. If it is, fix its status on the board and in the entry first, then pick the next one.
- Follow `CLAUDE.md` and `.claude/rules/*`: TDD, simplicity-first, provider-rules, docs, surgical-changes, no verification commands.
- Open questions come in three kinds:
  - **Default:** settled. Apply the answer without asking.
  - **Agent resolves:** look it up yourself (local docs, v1.1 docs or the sandbox) before implementing. It does not block.
  - No marker: a design decision for the user. Ask before implementing, and do not decide it alone.
- When the user answers a question, write the answer into the file as a **Default:** so later agents do not ask again.
- Vendus versions: the user decided on 2026-11-14 to migrate the package client to v1.2 as a separate change; until then, operations that exist only in v1.2 throw `OperationNotSupportedException` on Vendus. The package client calls v1.1 (`src/Configuration/HttpConfiguration.php`). The source of truth for v1.1 is the live spec at `https://www.vendus.pt/ws/v1.1/api.jsonapi` (rendered at `https://www.vendus.pt/ws/v1.1/`). The local `providers-docs/vendus/openapi.json` is v1.2 and has endpoints v1.1 lacks. Line numbers below point into the local v1.2 file; every Vendus endpoint in the backlog was checked against the live v1.1 spec on 2026-11-13.
- Moloni: the local pages under `providers-docs/moloni/` matched the live pages for every backlog endpoint on 2026-11-13.
- Status rules:
  1. Before starting, set the entry to `In progress` on the board and in the entry.
  2. If open questions need the user, set `Blocked (open questions)` on the board and in the entry, and list the questions in Notes.
  3. When the code, tests and docs are written, set `Done` on the board and in the entry. In Notes, list the files added and the docs pages updated, in the same change as the implementation.
  4. Board and entry statuses must agree and reflect the current source.

## Status board

| # | Operation | Priority | Status | Notes |
| --- | --- | --- | --- | --- |
| 1 | `Invoice::get` | MVP | Done | Added: `src/Contracts/IntegrationProvider/Invoice/ShouldGetInvoice.php`; `src/Provider/Moloni/Invoice.php` (base, `sequence()` moved from `Moloni/Invoice/Create.php`); `src/Provider/Moloni/Invoice/Get.php`; `src/Provider/CegidVendus/Invoice/Get.php`; `tests/Unit/Invoice/Get/IdTest.php`; `tests/Feature/Invoice/GetTest.php`; fixtures `Response/Invoice/get.json` for both providers. Changed: `src/Actions/InvoiceAction.php`; `src/Facades/Invoice.php`; `src/Provider/Moloni/Invoice/Create.php` (extends the new base). Docs: `docs/invoices/getting-an-invoice.md` (new); `docs/_sidebar.md`; `docs/invoices/README.md`; `docs/features.md` (new "Invoice Management" table); `docs/api-reference.md` |
| 2 | `Invoice::pdf` | MVP | Done | Added: `src/Contracts/IntegrationProvider/Invoice/ShouldGetInvoicePdf.php`; `src/Provider/CegidVendus/Invoice/Pdf.php`; `src/Provider/Moloni/Invoice/Pdf.php`; `tests/Unit/Invoice/Pdf/IdTest.php`; `tests/Feature/Invoice/PdfTest.php`; fixture `tests/Fixtures/IntegrationProvider/Moloni/Response/Invoice/pdf_link.json`. Changed: `src/Data/OutputData.php` (`url`); `src/Actions/InvoiceAction.php`; `src/Facades/Invoice.php`. Docs: `docs/invoices/outputting-invoice.md`; `docs/features.md`; `docs/api-reference.md` |
| 3 | `Invoice::cancel` | MVP | Done | Added: `src/Contracts/IntegrationProvider/Invoice/ShouldCancelInvoice.php`; `src/Provider/CegidVendus/Invoice/Cancel.php`; `src/Provider/Moloni/Invoice/Cancel.php`; `tests/Unit/Invoice/Cancel/PayloadTest.php`; `tests/Feature/Invoice/CancelTest.php`; fixture `tests/Fixtures/IntegrationProvider/Moloni/Response/Invoice/cancel.json`. Changed: `src/Actions/InvoiceAction.php`; `src/Facades/Invoice.php`. Docs: `docs/invoices/cancelling-an-invoice.md` (new); `docs/_sidebar.md`; `docs/invoices/README.md`; `docs/features.md`; `docs/api-reference.md` |
| 4 | `Invoice::find` | Post-MVP | Done | Added: `src/Contracts/IntegrationProvider/Invoice/ShouldFindInvoice.php`; `src/Provider/CegidVendus/Invoice.php` (base, `toInvoiceData()`); `src/Provider/CegidVendus/Invoice/Find.php`; `src/Provider/Moloni/Invoice/Find.php`; `tests/Unit/Invoice/Find/PayloadTest.php`; `tests/Feature/Invoice/FindTest.php`; fixtures `Response/Invoice/find.json` for both providers. Changed: `src/Provider/Moloni/Invoice.php` (`toInvoiceData()`); `src/Provider/Moloni/Invoice/Get.php`; `src/Provider/CegidVendus/Invoice/Get.php` (both use `toInvoiceData()`); `src/Actions/InvoiceAction.php`; `src/Facades/Invoice.php`. Docs: `docs/invoices/finding-invoices.md` (new); `docs/_sidebar.md`; `docs/invoices/README.md`; `docs/features.md`; `docs/api-reference.md` |
| 5 | `Client::update` | Post-MVP | Done | Added: `src/Contracts/IntegrationProvider/Client/ShouldUpdateClient.php`; `src/Provider/Moloni/Client/Update.php`; `src/Provider/CegidVendus/Client/Update.php`; `tests/Unit/Client/Update/ProviderData/PayloadTest.php`; `tests/Feature/Client/UpdateTest.php`. Fixtures reuse the existing `Response/Client/response.json` (Vendus) and `response_simple.json` (Moloni). Changed: `src/Actions/ClientAction.php`; `src/Facades/Client.php`; `src/Provider/Moloni/Client/Create.php` (`ensureRequiredData` message now "for a client", shared with update). Docs: `docs/clients/updating-a-client.md` (new); `docs/_sidebar.md`; `docs/clients/README.md`; `docs/features.md`; `docs/api-reference.md` |
| 6 | `Client::delete` | Post-MVP | Done | Added: `src/Contracts/IntegrationProvider/Client/ShouldDeleteClient.php`; `src/Provider/Moloni/Client/Delete.php`; `src/Provider/CegidVendus/Client/Delete.php`; `tests/Unit/Client/Delete/PayloadTest.php`; `tests/Feature/Client/DeleteTest.php`; fixture `tests/Fixtures/IntegrationProvider/Moloni/Response/Client/delete.json`. Changed: `src/Actions/ClientAction.php`; `src/Facades/Client.php`. Docs: `docs/clients/deleting-a-client.md` (new); `docs/_sidebar.md`; `docs/clients/README.md`; `docs/features.md`; `docs/api-reference.md` |
| 7 | `Item::delete` | Post-MVP | Done | Added: `src/Contracts/IntegrationProvider/Item/ShouldDeleteItem.php`; `src/Provider/Moloni/Item/Delete.php`; `src/Provider/CegidVendus/Item/Delete.php`; `tests/Unit/Item/Delete/PayloadTest.php`; `tests/Feature/Item/DeleteTest.php`; fixture `tests/Fixtures/IntegrationProvider/Moloni/Response/Item/delete.json`. Changed: `src/Provider/Base.php` (`$url`, `getUrl()`); `src/Actions/ItemAction.php`; `src/Facades/Item.php`. Docs: `docs/items/deleting-an-item.md` (new); `docs/_sidebar.md`; `docs/items/README.md`; `docs/features.md`; `docs/api-reference.md` |
| 8 | `Category::create` | Post-MVP | Done | Added: `src/Contracts/IntegrationProvider/Category/ShouldCreateCategory.php`; `src/Provider/CegidVendus/Category.php` (base, `toCategoryData()`); `src/Provider/CegidVendus/Category/Create.php`; `src/Provider/Moloni/Category/Create.php`; `tests/Unit/Category/Create/PayloadTest.php`; `tests/Feature/Category/CreateTest.php`; fixtures `tests/Fixtures/IntegrationProvider/CegidVendus/Response/Category/create.json` and `tests/Fixtures/IntegrationProvider/Moloni/Response/Category/create.json`. Changed: `src/Provider/Moloni/Category.php` (extends `Base`); `src/Actions/CategoryAction.php`; `src/Facades/Category.php`. Docs: `docs/categories/creating-a-category.md` (new); `docs/_sidebar.md`; `docs/categories/README.md`; `docs/features.md`; `docs/api-reference.md` |
| 9 | `Warehouse::find` | Post-MVP | Done | Added: `src/Contracts/IntegrationProvider/Warehouse/ShouldFindWarehouse.php`; `src/Data/WarehouseData.php`; `src/Provider/Moloni/Warehouse.php` and `src/Provider/CegidVendus/Warehouse.php` (bases, `toWarehouseData()`); `src/Provider/Moloni/Warehouse/Find.php`; `src/Provider/CegidVendus/Warehouse/Find.php`; `tests/Unit/Warehouse/Find/PayloadTest.php`; `tests/Feature/Warehouse/FindTest.php`; fixtures `Response/Warehouse/response_multiple.json` for both providers. Changed: `src/Actions/WarehouseAction.php` and `src/Facades/Warehouse.php` (new); `tests/Fixtures/Fixtures.php` (`warehouse()`). Docs: `docs/warehouses/README.md` and `docs/warehouses/finding-warehouses.md` (new); `docs/_sidebar.md`; `docs/features.md`; `docs/api-reference.md` |
| 10 | `Warehouse::create` | Post-MVP | Done | Moloni done; Cegid Vendus throws `OperationNotSupportedException` until entry 13 (v1.2 migration). Added: `src/Contracts/IntegrationProvider/Warehouse/ShouldCreateWarehouse.php`; `src/Provider/Moloni/Warehouse/Create.php`; `tests/Unit/Warehouse/StructureTest.php`; `tests/Unit/Warehouse/Create/PayloadTest.php`; `tests/Feature/Warehouse/CreateTest.php`; fixture `tests/Fixtures/IntegrationProvider/Moloni/Response/Warehouse/create.json`. Changed: `src/Data/WarehouseData.php` (`code`, `address`, `city`, `postalCode`, `country`, `isDefault`); `src/Actions/WarehouseAction.php`; `src/Facades/Warehouse.php`. Docs: `docs/warehouses/creating-a-warehouse.md` (new); `docs/warehouses/README.md`; `docs/_sidebar.md`; `docs/features.md`; `docs/api-reference.md` |
| 11 | `TaxExemption::find` | Post-MVP | Todo | Reference data |
| 12 | `DocumentType::find` | Post-MVP | Todo | Reference data |
| 13 | Cegid Vendus v1.2 migration | Post-MVP | Todo | Move the Vendus client from v1.1 to v1.2; unblocks Vendus `Warehouse::create` |

## Backlog

Each entry lists the operation, both provider endpoints, the existing pieces to reuse, a field mapping draft and the open questions.

Already exposed on both providers, so excluded: `Invoice::create` (FT, FR, RG, FS, NC; GT on Moloni), `Client::create`, `Client::get`, `Client::find`, `Item::create`, `Item::get`, `Item::find`, `Item::update`, `ItemStock::move`, `Category::find`, `Category::get`, `Unit::find`, `PaymentMethod::find`.

`Category::update` and `Unit::create` were removed from the backlog: Cegid Vendus v1.1 has no endpoint for them. They are listed under "Single-provider gaps".

### 1. Invoice::get

**Priority:** MVP
**Package status:** Implemented
**Status:** Done

**What it does:** Returns one document by its provider id so a developer can read back a created invoice.

**Moloni:** `documents/getOne` (`documents/documents/getOne`). Local docs: `providers-docs/moloni/dev/documents/documents/getone/index.html`. The endpoint is a search: required `company_id` only; `document_id` is one optional filter among others, and only the first match is returned. Send `document_id` only. `src/Provider/Moloni/Invoice/Create.php` already calls `documents/getOne` internally.

**Vendus:** `GET /documents/{id}`. v1.1 confirmed. `openapi.json` operation `get` on `/documents/{id}` (line 2068). Only path parameter `id`.

**Existing pieces to reuse:**
- `src/Facades/Invoice.php`, `src/Actions/InvoiceAction.php` (add `get` with the same `match` style)
- `src/Provider/Invoice.php` (base class for invoice operations)
- `src/Provider/Moloni/Invoice/Create.php` lines 72-89 (`documents/getOne` response mapping and `sequence()`)
- `src/Provider/CegidVendus/Invoice/Create.php` lines 76-86 (response mapping)
- `src/Contracts/IntegrationProvider/Client/ShouldGetClient.php` (contract pattern)
- `src/Provider/Moloni/Client/Get.php`, `src/Provider/CegidVendus/Client/Get.php` (get-by-id pattern)
- `src/Data/InvoiceData.php`

**Field mapping draft:**

| DTO field | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `id` | `document_id` | path `{id}` | Moloni `documents/documents/getone`; v1.1 `/documents/{id}` |
| `sequence` | built from `document_type.saft_code`, `document_set.name`, `number` (as `Create::sequence()`) | `number` | Moloni `getOne`; v1.1 `Documents_getItem` |
| `total`, `totalNet` | `net_value`, `net_value - taxes_value` | `amount_gross`, `amount_net` | as in the two `Create.php` files |

**Open questions:**
1. Should `Invoice::get` return the full `InvoiceData`, as `create()` does, or only `id` and `sequence`? **Default:** return the full `InvoiceData`.
2. Should the response fields the DTO does not list be kept with `fillAdditionalProperties()`, as `create()` does? **Default:** yes, keep them, as `create()` does.

**Done when:**
- [x] Pest tests in `tests/Unit/Invoice/Get/` and `tests/Feature/Invoice/`, using the `providers` dataset, with fixtures for both providers
- [x] Facade method, `InvoiceAction::get()` and contract added
- [x] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Invoice/` and `src/Provider/CegidVendus/Invoice/`
- [x] Docs page `docs/invoices/getting-an-invoice.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated
- [x] Board and entry statuses set to `Done`, with Notes filled

### 2. Invoice::pdf

**Priority:** MVP
**Package status:** Implemented
**Status:** Done

**What it does:** Returns the PDF of an existing document so a developer can store or print it.

**Moloni:** `documents/getPDFLink` (`documents/documents/getPDFLink`). Local docs: `providers-docs/moloni/dev/documents/documents/getpdflink/index.html`. Required: `company_id`, `document_id`. Optional: `signed` (unsigned when omitted). Returns `url`. The page states that documents in draft (status 0) cannot be requested.

**Vendus:** `GET /documents/{id}.pdf`. v1.1 confirmed: the operation description says "To obtain a pdf version, just add `.pdf` extension to the document's id." (`openapi.json` line 2088). Neither the v1.1 spec nor the old `documents.doc` page says whether the response is binary or base64. The old `documents.doc` page also lists optional `output` (`auto`, `pdf_url`, `pdf`, `html`, `escpos`, `tpasibs`, `json`) and `download` ("Download PDF. Only .pdf extension."); the v1.1 spec does not declare them as parameters (only in `x-codeSamples`).

**Existing pieces to reuse:**
- `src/Data/OutputData.php`, `src/Enums/OutputFormat.php` (output model)
- `src/Provider/CegidVendus/Invoice/Create.php` lines 154-157 (`buildOutput()`, `OutputFormat::vendus()`)
- `src/Provider/Moloni/Invoice/Create.php` (`documents/getOne` pattern)
- `src/Facades/Invoice.php`, `src/Actions/InvoiceAction.php`

Related gap: `TODO.md`, section "Moloni invoice create", already lists that no PDF is fetched on Moloni.

**Field mapping draft:**

| DTO field | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `id` | `document_id` | path `{id}.pdf` | Moloni `documents/documents/getpdflink`; v1.1 `/documents/{id}` |
| PDF result | `url` (download the file) | response body (binary or base64 unverified) | as above |

**Open questions:**
1. Should the result be a URL (Moloni's shape) or the PDF content (Vendus's shape), and how should `OutputData` carry it? **Default:** use the Vendus shape on both providers: return the PDF content in `OutputData` (`content`, `format`). On Moloni, download the file from the `getPDFLink` URL. If that is not possible, set the entry to `Blocked (open questions)` and ask the user.
2. Should the Moloni `signed` flag be a method argument, a DTO field, or left at the provider default? **Default:** leave it at the provider default and do not expose it.
3. Is the Vendus `.pdf` response binary or base64? Not stated in the v1.1 spec or the old docs. **Default:** resolved in the sandbox on 2026-11-14: `GET documents/{id}.pdf` returns the raw PDF (`content-type: application/pdf`, body starts with `%PDF`). `OutputData` with `PDF_BASE64` expects base64, so base64-encode the body. (`GET documents/{id}?output=pdf` returns JSON with a base64 `output` field, but v1.1 does not declare that parameter.)
4. The Moloni `getPDFLink` `url` (`https://www.moloni.pt/downloads/?h=<token>`) returns an HTML download page (`text/html`), not the PDF, with or without `&d=1`. Checked in the sandbox on 2026-11-14. That page links to `https://www.moloni.pt/downloads/index.php?action=getDownload&h=<token>&d=<document_id>`, which returns the PDF (`application/pdf`). That URL is not in the Moloni API docs. **Default:** return both the link and the PDF when available (user decision, 2026-11-14). `OutputData` gains a `url` field. On Moloni, `url` is the `getPDFLink` URL and `content` is the base64 PDF downloaded from the `getDownload` URL; when that download does not return `application/pdf`, `content` stays `null` and `url` is still returned. On Vendus, `url` is `null`. The operation returns `OutputData` via `getOutput()`.

**Done when:**
- [x] Pest tests in `tests/Unit/Invoice/Pdf/` and `tests/Feature/Invoice/`, using the `providers` dataset, with fixtures for both providers
- [x] Facade method, `InvoiceAction::pdf()` and contract added
- [x] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Invoice/` and `src/Provider/CegidVendus/Invoice/`
- [x] Docs page `docs/invoices/outputting-invoice.md` updated; `docs/features.md` matrix updated (Moloni "Save as PDF")
- [x] Board and entry statuses set to `Done`, with Notes filled

### 3. Invoice::cancel

**Priority:** MVP
**Package status:** Implemented
**Status:** Done

**What it does:** Sets an existing document to the cancelled state.

**Moloni:** `documents/documentCancel` (`documents/documents/documentCancel`). Local docs: `providers-docs/moloni/dev/documents/documents/documentcancel/index.html`. Sets status 2. Required: `company_id`, `document_id`. Optional: `observation`. Response: `valid`, `message`. Per the page, only documents that meet all of these can be cancelled: closed (status 1); not pending communication to the tax authority; no AT code (transport guides); no document derived from them; a document type that allows cancellation. These are provider rules: not handled in code, may be noted in the docs.

**Vendus:** `PATCH /documents/{id}` with body `status` (required). v1.1 confirmed. `openapi.json` operation `patch` on `/documents/{id}` (line 2108), request body `requestBodies.Documents_PATCH` (line 21998). `status` enum: `N` Normal, `A` Canceled, `F` Invoiced. Send `A`, as the `requests.doc` example `{"status":"A"}` and the old `documents.doc` status table show.

**Existing pieces to reuse:**
- `src/Facades/Invoice.php`, `src/Actions/InvoiceAction.php`
- `src/Provider/Invoice.php` (base class)
- `src/Provider/CegidVendus/Invoice/Create.php` (same `Http::provider()` and `Http::handleUnwantedFailures()` calls)
- `src/Contracts/IntegrationProvider/Item/ShouldUpdateItem.php` (update contract pattern)

**Field mapping draft:**

| DTO field | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `id` | `document_id` | path `{id}` | Moloni `documents/documents/documentcancel`; v1.1 `/documents/{id}` patch |
| (cancelled state) | `status` fixed at 2 by the endpoint | `status` = `A` | Moloni docs; v1.1 `Documents_PATCH` |
| `observation` (proposed) | `observation` | none | Moloni `documents/documents/documentcancel` |

**Open questions:**
1. Which Vendus `status` value cancels a document? **Default:** `A` (resolved from the v1.1 spec enum, `requests.doc` and the old `documents.doc`).
2. Should `observation` be a method argument or a DTO field? **Default:** a DTO field. Vendus has no equivalent, so it is dropped on Vendus per the unsupported-field rule.
3. Should the returned object be `InvoiceData` with the new status, or the provider response? **Default:** return nothing (`void`). Failures surface as the usual exceptions. (User decision, 2026-11-14.)
4. Which DTO field carries `observation`, and how is `void` expressed? **Default:** reuse `InvoiceData->notes` (no new field); sent only when set. The contract `ShouldCancelInvoice` declares `execute(): void` and does not extend `ShouldExecute`. A Moloni response without `valid: 1` throws `RequestFailedException` with the provider's `message`, as entry 6 does. (Agent decision, 2026-11-14.)

**Done when:**
- [x] Pest tests in `tests/Unit/Invoice/Cancel/` and `tests/Feature/Invoice/`, using the `providers` dataset, with fixtures for both providers
- [x] Facade method, `InvoiceAction::cancel()` and contract added
- [x] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Invoice/` and `src/Provider/CegidVendus/Invoice/`
- [x] Docs page `docs/invoices/cancelling-an-invoice.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated
- [x] Board and entry statuses set to `Done`, with Notes filled

### 4. Invoice::find

**Priority:** Post-MVP
**Package status:** Implemented
**Status:** Done

**What it does:** Lists documents, optionally filtered, so a developer can find an invoice without knowing its id.

**Moloni:** `documents/getAll` (`documents/documents/getAll`). Local docs: `providers-docs/moloni/dev/documents/documents/getall/index.html`. Required: `company_id`. Optional: `qty` (default 50), `offset` (default 0), `customer_id`, `supplier_id`, `salesman_id`, `document_set_id`, `number`, `date`, `expiration_date`, `year`, `your_reference`, `our_reference`, `status`. No document-type filter. `date` and `expiration_date` are single values, not a range.

**Vendus:** `GET /documents`. v1.1 confirmed. `openapi.json` line 1880. All filters optional: `store_id`, `register_id`, `client_id`, `client_fiscal_id`, `client_country`, `type`, `subtype`, `since`, `until`, `q`, `external_reference`, `status`, `view`, `mode`. Pagination per `requests.doc`: `page` and `per_page` (default 20, up to 1000); the response carries `X-Paginator-Items` and `X-Paginator-Pages` headers.

**Existing pieces to reuse:**
- `src/Provider/Moloni/Client/Find.php`, `src/Provider/CegidVendus/Client/Find.php` (list pattern)
- `src/Traits/HasPaginator.php`, `src/Contracts/ShouldHavePagination.php`, `src/Exceptions/Pagination/NoMorePagesException.php`
- `src/Actions/ClientAction.php` (`find()` pattern)
- `src/Enums/InvoiceType.php`

**Field mapping draft:**

| DTO field | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| document type filter | none (dropped on Moloni) | `type` | Moloni `getAll`; v1.1 `/documents` |
| client filter | `customer_id` | `client_id` | Moloni `getAll`; v1.1 `/documents` |
| date range | none (dropped on Moloni; only single `date`) | `since`, `until` | Moloni `getAll`; v1.1 `/documents` |
| pagination | `qty`, `offset` | `per_page`, `page` | Moloni `getAll`; v1.1 `requests.doc` |

**Open questions:**
1. Which filters belong on the filter DTO (document type, client, dates, store)? **Default:** the main ones: document type, client, and a date range (from/to). Drop any filter a provider lacks, per the unsupported-field rule. On Moloni this drops document type and date range.
2. What are the Moloni `getAll` filter parameters? **Default:** resolved, listed above.
3. Should the result be a paginated collection of `InvoiceData` or the raw provider list? **Default:** a paginated collection of `InvoiceData`, like the other finders.
4. How does the caller pass the filters? **Default:** reuse `InvoiceData`: `Invoice::find(?InvoiceData)`. The type filter always applies (`InvoiceData->type`, default `FT`); the client filter comes from `client->id`; there is no date range filter. (User decision, 2026-11-14; supersedes question 1.) Vendus sends `type` and `client_id`. Moloni picks the per-type endpoint, as `Create::endpoint()` does with `insert` (`invoices/getAll`, `invoiceReceipts/getAll`, `simplifiedInvoices/getAll`, `receipts/getAll`, `billsOfLading/getAll`, `creditNotes/getAll`; all exist in the local docs), and sends `customer_id`. The per-type responses carry the same fields as `documents/getOne`.

**Done when:**
- [x] Pest tests in `tests/Unit/Invoice/Find/` and `tests/Feature/Invoice/`, using the `providers` dataset, with fixtures for both providers
- [x] Facade method, `InvoiceAction::find()` and contract added
- [x] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Invoice/` and `src/Provider/CegidVendus/Invoice/`
- [x] Docs page `docs/invoices/finding-invoices.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated
- [x] Board and entry statuses set to `Done`, with Notes filled

### 5. Client::update

**Priority:** Post-MVP
**Package status:** Implemented
**Status:** Done

**What it does:** Updates a stored client's fields.

**Moloni:** `customers/update` (`entities/customers/update`). Local docs: `providers-docs/moloni/dev/entities/customers/update/index.html`. Required per the page: `company_id`, `customer_id`, `vat`, `number`, `name`, `language_id`, `address`, `city`, `country_id`, `maturity_date_id`, `payment_method_id`, `copies` (each with `document_type_id`, `copies`). Optional: `zip_code`, `email`, `website`, `phone`, `fax`, `contact_name`, `contact_email`, `contact_phone`, `notes`, `salesman_id`, `price_class_id`, `payment_day`, `discount`, `credit_limit`, `delivery_method_id`, `field_notes`. Changes to `vat` and `number` are ignored once the client is referenced by a document. Response: `valid`, `customer_id`.

**Vendus:** `PATCH /clients/{id}`. v1.1 confirmed. `openapi.json` line 1318. No required body field. v1.1 body: `fiscal_id`, `name`, `address`, `postalcode`, `city`, `phone`, `mobile`, `external_reference`, `notes`, `email`, `website`, `country` (ISO 3166-1 alpha-2), `price_group_id`, `send_email`, `default_pay_due`, `status`. (`irs_retention` and `manager_id` exist only in v1.2.)

**Existing pieces to reuse:**
- `src/Provider/Moloni/Client/Create.php`, `src/Provider/CegidVendus/Client/Create.php` (field mapping to copy)
- `src/Contracts/IntegrationProvider/Client/ShouldCreateClient.php` (contract pattern)
- `src/Data/ClientData.php`
- `src/Actions/ClientAction.php`
- `src/Provider/Moloni/Item/Update.php`, `src/Provider/CegidVendus/Item/Update.php` (update pattern)

**Field mapping draft:**

| DTO field | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `id` | `customer_id` | path `{id}` | Moloni `entities/customers/update`; v1.1 `/clients/{id}` |
| (caller-supplied number) | `number` | none | Moloni page; open question 4 |
| `vat` | `vat` | `fiscal_id` | Moloni page; as in both `Client/Create.php` files |
| `name` | `name` | `name` | as in both `Client/Create.php` files |
| `email`, `phone`, `address`, `city` | `email`, `phone`, `address`, `city` | `email`, `phone`, `address`, `city` | Moloni page; v1.1 `Clients_PATCH` |
| `postalCode` | `zip_code` | `postalcode` | Moloni page; v1.1 `Clients_PATCH` |
| `country` | `country_id` (needs the Moloni id) | `country` (ISO alpha-2) | Moloni page; v1.1 `Clients_PATCH` |

**Open questions:**
1. `ClientData` has no `number` field, and Moloni's update page lists `number` as required. Where does it come from? **Default:** the caller passes it. The package does not look it up and does not generate one. (User decision, 2026-11-14. The earlier default, "send `ClientData->id` as `number` as `Create.php` does", was wrong: `src/Provider/Moloni/Client/Create.php:110-112` sends `vat` plus 8 random characters when `id` is not valid, which would overwrite the number on update.)
2. Is update partial (send only the fields set) or a full replace? Vendus `PATCH` suggests partial. **Default:** full replace where the provider allows it: send every `ClientData` field.
3. How is `country` mapped? **Default:** copy `Client/Create.php` on each provider. Moloni looks up `country_id` from the ISO code with `Country/Get`, and Vendus sends the ISO code.
4. How does the caller pass Moloni `number`: a new field on `ClientData`, or a method argument? `ClientData->id` cannot carry it, because on update it is the Moloni `customer_id`. **Default:** through `ClientData->reference` (user decision, 2026-11-14). `externalReference` was renamed to `reference` in a separate change before this entry: it maps to Moloni `number` and to Vendus `external_reference`. `id` is always the provider client id (Moloni `customer_id`) on both providers, including the result of Moloni `Client::create`. Moloni create sends no `number` when `reference` is unset; the package does not generate one.

**Done when:**
- [x] Pest tests in `tests/Unit/Client/Update/` and `tests/Feature/Client/`, using the `providers` dataset, with fixtures for both providers
- [x] Facade method, `ClientAction::update()` and contract added
- [x] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Client/` and `src/Provider/CegidVendus/Client/`
- [x] Docs page `docs/clients/updating-a-client.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated (Update Client)
- [x] Board and entry statuses set to `Done`, with Notes filled

### 6. Client::delete

**Priority:** Post-MVP
**Package status:** Implemented
**Status:** Done

**What it does:** Deletes a stored client by id.

**Moloni:** `customers/delete` (`entities/customers/delete`). Local docs: `providers-docs/moloni/dev/entities/customers/delete/index.html`. Required: `company_id`, `customer_id`. A client referenced by a document cannot be deleted; the provider answers `valid: 0`.

**Vendus:** `DELETE /clients/{id}`. v1.1 confirmed in the spec (the old `clients.doc` example URL omits the id; the spec is authoritative). `openapi.json` line 1361. No request body, no response content.

**Existing pieces to reuse:**
- `src/Actions/ClientAction.php`
- `src/Provider/Moloni/Client/Get.php`, `src/Provider/CegidVendus/Client/Get.php` (by-id pattern)
- `src/Contracts/IntegrationProvider/Client/ShouldGetClient.php` (contract pattern)

**Field mapping draft:**

| DTO field | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `id` | `customer_id` | path `{id}` | Moloni `entities/customers/delete`; v1.1 `/clients/{id}` delete |

**Open questions:**
1. What does the operation return: nothing, or a boolean? **Default:** return nothing (`void`). Failures surface as the usual exceptions.
2. On Moloni, `valid: 0` for a referenced client: which domain exception should the package throw? **Default:** reuse `RequestFailedException`, carrying the provider's message. Add no new exception type.

**Done when:**
- [x] Pest tests in `tests/Unit/Client/Delete/` and `tests/Feature/Client/`, using the `providers` dataset, with fixtures for both providers
- [x] Facade method, `ClientAction::delete()` and contract added
- [x] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Client/` and `src/Provider/CegidVendus/Client/`
- [x] Docs page `docs/clients/deleting-a-client.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated (Delete Client)
- [x] Board and entry statuses set to `Done`, with Notes filled

### 7. Item::delete

**Priority:** Post-MVP
**Package status:** Missing
**Status:** Done

**What it does:** Deletes a stored item (product) by id.

**Moloni:** `products/delete` (`products/products/delete`). Local docs: `providers-docs/moloni/dev/products/products/delete/index.html`. Required: `company_id`, `product_id`.

**Vendus:** `DELETE /products/{id}`. v1.1 confirmed in the spec (the old `products.doc` example URL omits the id; the spec is authoritative). `openapi.json` line 2660. No request body, no response content.

**Existing pieces to reuse:**
- `src/Actions/ItemAction.php`
- `src/Provider/Moloni/Item/Update.php`, `src/Provider/CegidVendus/Item/Update.php` (provider class pattern)
- `src/Contracts/IntegrationProvider/Item/ShouldUpdateItem.php` (contract pattern)

**Field mapping draft:**

| DTO field | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `id` | `product_id` | path `{id}` | Moloni `products/products/delete`; v1.1 `/products/{id}` delete |

**Open questions:**
1. What does the operation return: nothing, or a boolean? **Default:** return nothing (`void`). Failures surface as the usual exceptions.

**Done when:**
- [x] Pest tests in `tests/Unit/Item/Delete/` and `tests/Feature/Item/`, using the `providers` dataset, with fixtures for both providers
- [x] Facade method, `ItemAction::delete()` and contract added
- [x] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Item/` and `src/Provider/CegidVendus/Item/`
- [x] Docs page `docs/items/deleting-an-item.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated (Delete Item)
- [x] Board and entry statuses set to `Done`, with Notes filled

### 8. Category::create

**Priority:** Post-MVP
**Package status:** Implemented
**Status:** Done

**What it does:** Creates a product category.

**Moloni:** `productCategories/insert` (`products/product-categories/insert`). Local docs: `providers-docs/moloni/dev/products/product-categories/insert/index.html`. Required: `company_id`, `parent_id`, `name`. Optional: `description`, `pos_enabled`. Response: `valid`, `category_id`.

**Vendus:** `POST /products/categories`. v1.1 confirmed. v1.1 body: `title` (required), `status` (`on`, `off`, `delete`). No description or parent field. (`color`, `allow_kitchen_request`, `kitchens_id` exist only in v1.2.) `openapi.json` line 5385.

**Existing pieces to reuse:**
- `src/Actions/CategoryAction.php`
- `src/Data/CategoryData.php` (already has `id`, `name`, `parentId`)
- `src/Provider/Moloni/Category/Get.php`, `src/Provider/CegidVendus/Category/Get.php` (provider class pattern)
- `src/Contracts/IntegrationProvider/Category/ShouldGetCategory.php` (contract pattern)

**Field mapping draft:**

| DTO field | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `name` | `name` | `title` | Moloni `products/product-categories/insert`; v1.1 `/products/categories` post |
| `parentId` | `parent_id` (required) | none (dropped on Vendus) | as above |
| `description` (proposed) | `description` | none (dropped on Vendus) | as above |

**Open questions:**
1. Moloni requires `parent_id`. Does `CategoryData` need a parent field? **Default:** no change: `CategoryData` already has `parentId` (`src/Data/CategoryData.php:18`). Map it to Moloni `parent_id`; drop it on Vendus.
2. Which fields does the Vendus body accept? **Default:** resolved: v1.1 accepts `title` and `status` only.
3. What does the operation return: the created `CategoryData` or the provider response? **Default:** return the DTO, as the existing `create` and `find` operations do.

**Done when:**
- [x] Pest tests in `tests/Unit/Category/Create/` and `tests/Feature/Category/`, using the `providers` dataset, with fixtures for both providers
- [x] Facade method, `CategoryAction::create()` and contract added
- [x] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Category/` and `src/Provider/CegidVendus/Category/`
- [x] Docs page `docs/categories/creating-a-category.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated
- [x] Board and entry statuses set to `Done`, with Notes filled

### 9. Warehouse::find

**Priority:** Post-MVP
**Package status:** Implemented
**Status:** Done

**What it does:** Lists the warehouses (Moloni) or stores (Vendus) so a developer can pick one by id.

**Moloni:** `warehouses/getAll` (`settings/warehouses/getall`). Local docs: `providers-docs/moloni/dev/settings/warehouses/getall/index.html`. Required: `company_id`. Response fields include `warehouse_id`, `title`, `is_default`, `code`.

**Vendus:** `GET /stores` and `GET /stores/{id}`. v1.1 confirmed. `openapi.json` lines 3254 and 3330. Optional `type` filter (`all`, `store`, `warehouse`). Response fields include `id`, `type`, `title`.

**Existing pieces to reuse:**
- `src/Actions/UnitAction.php` (single `find()` pattern)
- `src/Provider/Moloni/Unit/Find.php`, `src/Provider/CegidVendus/Unit/Find.php` (list pattern)
- No Warehouse DTO exists in `src/Data/`. A new DTO is needed.

**Field mapping draft:**

| DTO field (proposed) | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `id` | `warehouse_id` | `id` | Moloni `settings/warehouses/getall`; v1.1 `/stores` |
| `name` | `title` | `title` | as above |

**Open questions:**
1. Facade name: `Store` (Vendus) or `Warehouse` (Moloni)? **Default:** `Warehouse`. The package already uses `warehouseId` (`StockMovementData`, Moloni `ItemStock/Move.php`), and it is the usual name for a stock location.
2. Which fields should the new DTO carry? **Default:** `WarehouseData` with `id` and `name` only. Keep extra response fields with `fillAdditionalProperties()`. Do not expose the Vendus `type` filter.

**Done when:**
- [x] Pest tests in `tests/Unit/Warehouse/Find/` and `tests/Feature/Warehouse/`, using the `providers` dataset, with fixtures for both providers
- [x] Facade, action and contract added, and a new DTO in `src/Data/`
- [x] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Warehouse/` and `src/Provider/CegidVendus/Warehouse/`
- [x] Docs page `docs/warehouses/finding-warehouses.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated (new section)
- [x] Board and entry statuses set to `Done`, with Notes filled

### 10. Warehouse::create

**Priority:** Post-MVP
**Package status:** Implemented
**Status:** Done

**What it does:** Creates a warehouse (Moloni).

**Moloni:** `warehouses/insert` (`settings/warehouses/insert`). Local docs: `providers-docs/moloni/dev/settings/warehouses/insert/index.html`. Required: `company_id`, `title`, `code`, `address`, `city`, `zip_code`, `country_id`. Optional: `is_default` (1/0), `phone`, `fax`, `contact_name`, `contact_email`. Response: `valid`, `warehouse_id`. Creating the first warehouse moves all stock to it.

**Vendus:** a Vendus store is a record tied to the account subscription; its `type` is `store` or `warehouse`. Both map to the package `Warehouse` (see `GLOSSARY.md`). Creating one is `POST /stores`, which exists only in v1.2 (local file line 3297); the live v1.1 spec has `GET /stores` and `GET /stores/{id}` only (checked 2026-11-14). The package client calls v1.1, so Vendus throws `OperationNotSupportedException` until the package migrates to v1.2.

**Existing pieces to reuse:**
- Warehouse::find (entry 9) for the DTO and facade
- `src/Actions/TaxAction.php` (`throw new OperationNotSupportedException` for one provider)
- `src/Provider/Moloni/Unit/Find.php` (provider class pattern)

**Field mapping draft:**

| DTO field (proposed) | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `name` | `title` | n/a | Moloni `settings/warehouses/insert` |
| n/a | generated by the provider class (random 8 chars), returned in additional data | n/a | as above |
| `address`, `city`, `postalCode` | `address`, `city`, `zip_code` | n/a | as above |
| `country` | `country_id` (needs the Moloni id) | n/a | as above |

**Open questions:**
1. Facade name: `Store` or `Warehouse` (same decision as entry 9)? **Default:** `Warehouse`, as in entry 9.
2. Moloni needs `country_id`, not an ISO code. Should the DTO carry the ISO code and look up the id, as `Invoice::create` does for transport? **Default:** yes. Carry the ISO code and look up the id, as `Invoice::create` and `Client::create` do.
3. How does Vendus take the default store flag? **Default:** resolved: Vendus v1.1 has no create endpoint at all.
4. What happens on Cegid Vendus? **Default:** migrate the package client to Vendus v1.2, then add the Vendus implementation on `POST /stores` (user decision, 2026-11-14). The migration is a separate change. Until it lands, the action throws `OperationNotSupportedException`, as `TaxAction::create()` does, and the docs say so.
5. How is the country id resolved, given unit tests cannot fake HTTP? **Default:** `buildPayload()` runs in the constructor without HTTP; `execute()` looks up `country_id` with `Country/Get` before sending. The payload unit test does not include `country_id`; the feature test covers it. Fields: `address`, `city`, `postalCode`, `country` added to `WarehouseData` (shared with Vendus v1.2 `POST /stores`). Moloni `code` is required but Moloni-only, so `Create` generates a random 8-character code and returns it in additional data; `is_default`, phone, fax and contact fields are not sent (user decision, 2026-11-14: no DTO field for one provider only).

**Done when:**
- [x] Pest tests in `tests/Unit/Warehouse/Create/` and `tests/Feature/Warehouse/`, using the `providers` dataset: Moloni fixtures, and a Vendus test asserting `OperationNotSupportedException`
- [x] Facade method, action and contract added, using the DTO from entry 9
- [x] Moloni implementation added under `src/Provider/Moloni/Warehouse/`
- [x] Docs page `docs/warehouses/creating-a-warehouse.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated (Vendus not supported)
- [x] Board and entry statuses set to `Done`, with Notes filled

### 11. TaxExemption::find

**Priority:** Post-MVP
**Package status:** Missing
**Status:** Todo

**What it does:** Lists the tax exemption reasons stored in the provider.

**Moloni:** `taxExemptions/getAll` (`global-data/tax-exemptions/getall`). Local docs: `providers-docs/moloni/dev/global-data/tax-exemptions/getall/index.html`. No required parameters. Optional `with_invisible`. Response fields: `code`, `name`, `description`, `visibility`.

**Vendus:** `GET /taxes/exemptions`. v1.1 confirmed. `openapi.json` line 8300. No parameters. Response fields: `id` (e.g. `M40`), `text`, `law`.

**Existing pieces to reuse:**
- `src/Enums/Tax/TaxExemptionReason.php` (package enum of reasons)
- `src/Actions/TaxAction.php` (`find()` pattern)
- `src/Provider/Moloni/Tax/Find.php` (provider class pattern)
- `src/Contracts/IntegrationProvider/Tax/ShouldFindTax.php` (contract pattern)

**Field mapping draft:**

| DTO field | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| `code` | `code` | `id` | Moloni `global-data/tax-exemptions/getall`; v1.1 `/taxes/exemptions` |
| `description` | `description` | `text` | as above |
| `with_invisible` (optional) | `with_invisible` | none | Moloni page |

**Open questions:**
1. How does the Moloni list relate to the package's `TaxExemptionReason` enum? Should the find operation return the provider's list or match the enum? **Default:** return the provider's list. Map each code to `src/Enums/Tax/TaxExemptionReason.php` when it matches; keep the provider code and description as given.
2. What is the default for `with_invisible`? **Default:** leave it at the provider default and do not expose it.
3. What is the returned DTO? **Default:** a new `TaxExemptionData` with `code` and `description`.

**Done when:**
- [ ] Pest tests in `tests/Unit/TaxExemption/Find/` and `tests/Feature/TaxExemption/`, using the `providers` dataset, with fixtures for both providers
- [ ] Facade method, `TaxAction::findExemptions()` (or agreed name) and contract added
- [ ] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/Tax/` and `src/Provider/CegidVendus/Tax/`
- [ ] Docs page `docs/taxes/finding-tax-exemptions.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated
- [ ] Board and entry statuses set to `Done`, with Notes filled

### 12. DocumentType::find

**Priority:** Post-MVP
**Package status:** Missing
**Status:** Todo

**What it does:** Lists the document types the provider supports (FT, FR, NC and so on).

**Moloni:** `documents/getAllDocumentTypes` (`documents/documents/getalldocumenttypes`). Local docs: `providers-docs/moloni/dev/documents/documents/getalldocumenttypes/index.html`. Required: `language_id`. Response fields: `document_type_id`, `saft_code`, `name`.

**Vendus:** `GET /documents/types`. v1.1 confirmed. `openapi.json` line 4891. Optional `visible_filter`, `visible_finalize` (`yes`, `no`). Response fields: `id` (two-letter code), `title`, `visible_filter`, `visible_finalize`.

**Existing pieces to reuse:**
- `src/Enums/InvoiceType.php` (the package's document types)
- `src/Provider/Moloni/Language/Get.php` (internal language lookup, source of `language_id`)
- `src/Provider/Moloni/Unit/Find.php`, `src/Provider/CegidVendus/Unit/Find.php` (list pattern)

**Field mapping draft:**

| DTO field (proposed) | Moloni field | Vendus field | Docs reference |
| --- | --- | --- | --- |
| language | `language_id` (required) | none | Moloni `documents/documents/getalldocumenttypes` |
| `code` | `saft_code` | `id` | Moloni page; v1.1 `/documents/types` |
| `name` | `name` | `title` | as above |

**Open questions:**
1. Where does `language_id` come from for Moloni? It is required and the package has no language option. **Default:** take an optional `LanguageData` and fall back to `pt`. Look up the id with `Moloni/Language/Get`, as `src/Provider/Moloni/Client/Create.php` does.
2. Is the return a DTO or the raw list? No DTO exists in `src/Data/`. **Default:** return a new DTO, like the other finders.

**Done when:**
- [ ] Pest tests in `tests/Unit/DocumentType/Find/` and `tests/Feature/DocumentType/`, using the `providers` dataset, with fixtures for both providers
- [ ] Facade, action and contract added, and a DTO in `src/Data/` if needed
- [ ] Moloni and Cegid Vendus implementations added under `src/Provider/Moloni/` and `src/Provider/CegidVendus/`
- [ ] Docs page `docs/document-types/finding-document-types.md` added; `docs/_sidebar.md` and the `docs/features.md` matrix updated (new section)
- [ ] Board and entry statuses set to `Done`, with Notes filled

### 13. Cegid Vendus v1.2 migration

**Priority:** Post-MVP
**Package status:** Missing
**Status:** Todo

**What it does:** Moves the Cegid Vendus client from API v1.1 to v1.2, so the package can use the endpoints that exist only in v1.2. User decision, 2026-11-14 (see entry 10, question 4).

**Scope:**
- Base URL: `src/Configuration/HttpConfiguration.php:33` sets `https://www.vendus.pt/ws/v1.1/`.
- Every Cegid Vendus provider class (27 files under `src/Provider/CegidVendus/`): request payloads and response mapping move from the v1.1 flat fields (`gross_price`, `tax_id`) to the v1.2 nested fields (`prices.gross`, `tax.id`).
- Every Cegid Vendus fixture under `tests/Fixtures/IntegrationProvider/CegidVendus/` and the tests that assert Vendus field names.
- Docs: every Cegid Vendus section that names a field or endpoint, and the "Vendus versions" notes in `CLAUDE.md`, `AGENTS.md` and this file.
- Source of truth after the move: the local `providers-docs/vendus/openapi.json` (v1.2).

**Unblocked by this entry:**
- `Warehouse::create` on Cegid Vendus (`POST /stores`); entry 10 currently throws `OperationNotSupportedException`.
- Single-provider gaps that are v1.2 only: `Category::update` (`PATCH /products/categories/{id}`), `Unit::create` (`POST /products/units`), `DueDate::find` (`GET /documents/duedates`).

**Open questions:**
1. Is this one change, or one change per resource (Client, Item, Invoice, ...) while the rest stays on v1.1? Mixing versions needs a per-request base URL.
2. Does the Vendus sandbox accept the current API key on v1.2, and do v1.2 responses match the local spec? **Agent resolves:** check in the sandbox before mapping.
3. Do the v1.2-only operations listed above get their own entries after the migration, or are they part of this one?

**Done when:**
- [ ] The Vendus client calls v1.2, and every Vendus payload and response mapping uses the v1.2 field names
- [ ] Vendus fixtures and tests updated to v1.2 shapes
- [ ] Docs and agent instructions updated; no reference to v1.1 as the current version remains
- [ ] Board and entry statuses set to `Done`, with Notes filled

## Single-provider gaps

Operations that one provider does not support: either the package already exposes them on one provider, or they exist only on Moloni. Not duplicated in the backlog.

| Operation | Missing on | Provider endpoint | Reference |
| --- | --- | --- | --- |
| `PaymentMethod::create` | Cegid Vendus | `POST /documents/paymentmethods` | `TODO.md`, "Operations not supported by a provider" |
| `DueDate::find` | Cegid Vendus | none in v1.1 (`GET /documents/duedates` exists only in v1.2) | `TODO.md`; unblocked by entry 13 |
| `DueDate::create` | Cegid Vendus | none in `openapi.json` | `TODO.md` (cannot implement until Vendus adds an endpoint) |
| `Tax::find` | Cegid Vendus | `GET /taxes` (openapi line 3606) | `docs/features.md` shows Vendus not supported. Not in `TODO.md` |
| `Tax::create` | Cegid Vendus | none in `openapi.json` | `docs/features.md`. Not in `TODO.md`; cannot implement until Vendus adds an endpoint |
| `Company::find` | Cegid Vendus | `GET /account` (line 1010), mapping unverified | `docs/features.md` shows Vendus not supported. Not in `TODO.md` |
| `Category::update` | Cegid Vendus | none in v1.1 (`PATCH /products/categories/{id}` exists only in v1.2, line 5458) | Unblocked by entry 13. Not exposed yet. Moloni: `productCategories/update` (required `company_id`, `category_id`, `parent_id`, `name`) |
| `Unit::create` | Cegid Vendus | none in v1.1 (`POST /products/units` exists only in v1.2, line 6199) | Unblocked by entry 13. Not exposed yet. Moloni: `measurementUnits/insert` (required `company_id`, `name`, `short_name`) |

Moloni invoice PDF and the Moloni "Save as PDF" gap are covered by backlog entry 2 (`Invoice::pdf`), not listed here. `Warehouse::create` is Moloni only until entry 13, and stays in the backlog as entry 10.

## Provider-only features

Not in the backlog. Listed for reference only.

- **Cegid Vendus:** ESC/POS output; client balance and resume; registers and movements; kitchens and print requests; stores tables and rooms; reports; discount cards; variants and variant groups; self-consumption; transport permit; SAFT export; tax-free documents; account users and settings.
- **Moloni:** debit notes; estimates; delivery notes; bills of lading (GT is already exposed); waybills; purchase orders; supplier documents; Multibanco references; customer alternate addresses; salesmen; suppliers; bank accounts; CAE codes; deductions; delivery methods; vehicles; price classes; product stocks by warehouse; countries; currencies; languages; fiscal zones; document models; product properties.
- **Moloni, internal only (no facade):** `DocumentSeries/Find`, `Country/Get`, `Language/Get`, `PaymentReturn/Create`. Their provider classes exist in `src/Provider/Moloni/`; they are not exposed as facades.
