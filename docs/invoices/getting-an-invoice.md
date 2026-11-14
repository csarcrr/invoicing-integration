# Getting an Invoice

Use `Invoice::get()` to read back one stored document by its provider ID, for example a document created earlier with `Invoice::create()`.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::get(InvoiceData::make(['id' => 123456]))->execute()->getInvoice();

$invoice->id;
$invoice->sequence;  // e.g. "FR A/12" on Moloni, "FR 01P2026/12" on Cegid Vendus
$invoice->total;     // gross total in cents
$invoice->totalNet;  // net total in cents
$invoice->type;      // InvoiceType, FT when the provider type is unknown
$invoice->atcudHash; // Cegid Vendus only; null on Moloni
```

- Pass the provider ID (`id`), not the sequence.
- Without an `id`, `execute()` throws `InvalidArgumentException` with `Invoice ID is required.`
- When the provider has no document with that ID, `execute()` throws `RequestFailedException`.
- `output` is not filled. `Invoice::get()` does not fetch the PDF or the ESC/POS output.
- Response fields the package does not map are kept with `getAdditionalData()`.

## Cegid Vendus

The request is sent to `GET documents/{id}`. `id` is mapped from `id`, `sequence` from `number`, `total` from `amount_gross` and `totalNet` from `amount_net`. `atcudHash` is mapped from `atcud`. `type` is mapped from `type`.

## Moloni

The request is sent to `documents/getOne` with `document_id`. Moloni returns only the first document that matches, so the package sends only `document_id`.

`id` is the ID you passed. `sequence` is built from `document_type.saft_code`, `document_set.name` and `number`, as on `Invoice::create()`. `total` is mapped from `net_value` and `totalNet` is `net_value - taxes_value`. `type` is mapped from `document_type.saft_code`. `atcudHash` is `null`, because Moloni does not return the ATCUD.

## Reference

See also:

- [Invoices Overview](README.md)
- [Creating an Invoice](creating-an-invoice.md)
- [API Reference – ShouldGetInvoice](../api-reference.md#shouldgetinvoice-contract)
