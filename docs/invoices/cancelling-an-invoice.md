# Cancelling an Invoice

Use `Invoice::cancel()` to set a stored document to the cancelled state, by its provider ID.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

Invoice::cancel(InvoiceData::make(['id' => 123, 'notes' => 'Wrong client']))->execute();
```

- Pass the provider ID (`id`), not the sequence.
- `execute()` returns nothing. Check the outcome through the exceptions below.
- `notes` is optional. Moloni sends it as the cancellation `observation`; Cegid Vendus does not send it.
- Without an `id`, `Invoice::cancel()` throws `InvalidArgumentException` with `Invoice ID is required.`
- When the provider rejects the cancellation, `execute()` throws `RequestFailedException`.

## Cegid Vendus

The request is sent to `PATCH documents/{id}` with `status` set to `A` (Canceled). `notes` is not sent, because Cegid Vendus has no equivalent field.

## Moloni

The request is sent to `documents/documentCancel` with `document_id`. `notes` is sent as `observation` when set.

Moloni returns `valid` and `message`. When `valid` is not `1`, `execute()` throws `RequestFailedException` with the provider `message`.

> [!NOTE]
> Moloni only cancels a document when all of these are true. The package does not check them; the provider rejects the request when one is not met:
>
> - The document is closed (status 1).
> - The document is not pending communication to the tax authority.
> - The document has no AT code (transport guides).
> - The document has not given rise to another document.
> - The document type allows cancellation.

## Reference

See also:

- [Invoices Overview](README.md)
- [Getting an Invoice](getting-an-invoice.md)
- [API Reference – ShouldCancelInvoice](../api-reference.md#shouldcancelinvoice-contract)
