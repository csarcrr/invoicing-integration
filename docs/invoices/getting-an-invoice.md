# Getting an Invoice

Use `Invoice::fetch()` to read back one stored document by its provider ID, for example a document created earlier with `Invoice::create()`.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::fetch(InvoiceData::make(['id' => 123456]))->execute()->get();

$invoice->id;
$invoice->sequence;  // e.g. "FR A/12" on Moloni, "FR 01P2026/12" on Cegid Vendus
$invoice->total;     // gross total in cents
$invoice->totalNet;  // net total in cents
$invoice->type;      // ?InvoiceType, null when the provider type is unknown
$invoice->atcud;     // null when the provider returns no ATCUD or an empty one
```

- Pass the provider ID (`id`), not the sequence.
- Without an `id`, `Invoice::fetch()` throws `InvalidArgumentException` with `Invoice ID is required.`
- When the provider has no document with that ID, `execute()` throws `RequestFailedException`.
- `output` is not filled. `Invoice::fetch()` does not fetch the PDF or the ESC/POS output.
- Response fields the package does not map are kept with `getAdditionalData()`.

## Cegid Vendus

The request is sent to `GET documents/{id}`. `id` is mapped from `id`, `sequence` from `number`, `total` from `amount_gross` and `totalNet` from `amount_net`. `atcud` is mapped from `atcud`. `type` is mapped from `type`; an unknown type is `null`.

## Moloni

The request is sent to `documents/getOne` with `document_id`. Moloni returns only the first document that matches, so the package sends only `document_id`.

`id` is the ID you passed. `sequence` is built from `document_type.saft_code`, `document_set.name` and `number`, as on `Invoice::create()`. `total` is mapped from `net_value` and `totalNet` is `net_value - taxes_value`. `type` is mapped from `document_type.saft_code`; an unknown type is `null`. `atcud` is mapped from `atcud`, and an empty value is `null`.

## Reference

See also:

- [Invoices Overview](README.md)
- [Creating an Invoice](creating-an-invoice.md)
- [API Reference – ShouldFetchInvoice](../api-reference.md#shouldfetchinvoice-contract)
