# Finding Invoices

Search and paginate the documents stored in the provider without leaving your
Laravel app. The `Invoice::find()` call returns a paginator whose list holds
`InvoiceData` instances, one per stored document.

## When to Use

Use `find()` when you need to:

- List the invoices of one document type stored in the provider
- List the invoices of one client
- Find an invoice without knowing its provider ID

If you already know the provider ID, prefer `Invoice::fetch()` for a direct lookup.

## Basic Usage

```php
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$results = Invoice::find()->execute();

foreach ($results->get() as $invoice) {
    // do stuff
}
```

- `get()` returns a `Collection` of `InvoiceData` instances populated by
  the provider response.
- The same paginator object is returned, so you can immediately call
  `next()->execute()` to fetch additional pages.
- Each returned invoice has the same fields as an invoice from
  `Invoice::fetch()`. See [Getting an Invoice](getting-an-invoice.md#basic-usage).
- Response fields the package does not map are kept with `getAdditionalData()`.

## Filtering by Type

The filter is the `type` of `InvoiceData`. Without a type, no document type is
sent and the provider decides what to list.

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$results = Invoice::find(InvoiceData::make(['type' => InvoiceType::InvoiceReceipt]))->execute();
```

- `type` accepts any `InvoiceType` case: FT, FR, FS, RG, GT and NC.
- There is no date filter.

## Filtering by Client

Set `client` to a `ClientData` with the provider client ID in `id`. Without a
client, the list is not filtered by client.

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$results = Invoice::find(InvoiceData::make(['client' => ['id' => '12001']]))->execute();
```

- The client filter uses `id`, the provider client ID, as returned by
  `Client::find()` or `Client::fetch()`. It does not use the VAT number.

## Pagination API

`Invoice::find()` implements `next()`, `previous()`, and `page()` helpers through
the `HasPaginator` trait.

```php
$results = Invoice::find()->execute();

while ($results->get()->isNotEmpty()) {
    // do stuff

    if ($results->getCurrentPage() === $results->getTotalPages()) {
        break;
    }

    $results->next()->execute();
}
```

- `getTotalPages()` reads the provider's `X-Paginator-Pages` header on Cegid
  Vendus (defaults to `1` if absent). It returns `null` on Moloni, which does
  not report a total, and before the first `execute()`: keep calling `next()`
  until a page comes back empty.
- Calling `previous()` before page 1, or `page()` below 1, raises
  `NoMorePagesException`. On Cegid Vendus, after the first `execute()`,
  `next()` beyond the last page and `page()` above the total also raise it. On
  Moloni they return an empty list.

## Cegid Vendus

The request is sent to `GET documents` with these query parameters:

- `type`: sent only when `InvoiceData` has a `type`.
- `client_id`: sent only when `client` has an `id`.
- `page`: the current page.

The page size is the provider default; the package does not send `per_page`.

When nothing matches, Cegid Vendus answers `404` with error code `A001`
(`No data`). The package returns an empty list instead of throwing. Any other
`404` throws `RequestFailedException`.

## Moloni

Moloni has one list endpoint per document type. The package picks it from the
`type` of `InvoiceData`:

| `InvoiceType` | Code | Endpoint |
| --- | --- | --- |
| `Invoice` | FT | `invoices/getAll` |
| `InvoiceReceipt` | FR | `invoiceReceipts/getAll` |
| `InvoiceSimple` | FS | `simplifiedInvoices/getAll` |
| `Receipt` | RG | `receipts/getAll` |
| `Transport` | GT | `billsOfLading/getAll` |
| `CreditNote` | NC | `creditNotes/getAll` |
| none | - | `documents/getAll` |

- `client` sends `customer_id`, the Moloni customer ID, when `id` is set.
- Pagination sends `qty` and `offset`. The package sends `qty` as 20 documents
  per page, and `offset` as `(page - 1) * qty`. Moloni accepts at most 50 per
  page.
- Moloni does not report a total, so `getTotalPages()` returns `null`.

## Field Mapping

Each returned invoice maps the provider fields as
[Getting an Invoice](getting-an-invoice.md) does:

- `id` is the provider document ID.
- `sequence` is `number` on Cegid Vendus, and is built from the document type
  code, the document set name and `number` on Moloni.
- `total` and `totalNet` are in cents.
- `type` is mapped from the provider document type. It is `null` when the type
  is unknown.
- `atcud` is mapped from `atcud`. It is `null` when the provider returns no ATCUD or an empty one.

## Putting It Together

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoices = Invoice::find(InvoiceData::make([
    'type' => InvoiceType::InvoiceReceipt,
    'client' => ['id' => '12001'],
]))->execute();

while ($invoices->get()->isNotEmpty()) {
    foreach ($invoices->get() as $invoice) {
        // do stuff
    }

    if ($invoices->getCurrentPage() === $invoices->getTotalPages()) {
        break;
    }

    $invoices->next()->execute();
}
```

## Reference

See also:

- [Invoices Overview](README.md)
- [Getting an Invoice](getting-an-invoice.md)
- [API Reference – ShouldFindInvoice](../api-reference.md#shouldfindinvoice-contract)
