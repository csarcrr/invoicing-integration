# Invoicing Integration Documentation

Welcome to the documentation for the Invoicing Integration package. This package provides a unified, fluent API for integrating with invoicing systems in Portugal.

**Supported Providers:** Cegid Vendus

## Important Legal Disclaimer

**This package facilitates invoicing provider API usage and is not intended to serve as legal guidance.**

It is **your responsibility** to:

- Comply with all applicable invoicing laws and regulations in your jurisdiction
- Understand each provider's specific requirements
- Ensure proper invoicing practices according to your legal obligations
- Validate that your usage complies with tax laws and accounting standards

Always consult with legal and accounting professionals when implementing invoicing solutions.

## Table of Contents

- [Getting Started](getting-started.md) - Installation and configuration
- [Features](features.md) - Supported features by provider and document type

### Clients

- [Overview](clients/README.md)
- [Creating a Client](clients/creating-a-client.md)
- [Getting a Client](clients/getting-a-client.md)
- [Finding Clients](clients/finding-clients.md)

### Invoices

- [Creating an Invoice](invoices/creating-an-invoice.md)
- [Creating a Receipt (RG)](invoices/creating-a-RG-for-an-invoice.md)
- [Creating a Credit Note (NC)](invoices/creating-a-nc-invoice.md)
- [Output Formats](invoices/outputting-invoice.md)
- [Using Invoice Data](invoices/using-invoice-data.md)

### Providers

- [Cegid Vendus Configuration](providers/cegid-vendus/configuration.md)

### Reference

- [API Reference](api-reference.md)

## Quick Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 1000,
    'quantity' => 2,
]);
$invoice->item($item);

$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 2000,
]);
$invoice->payment($payment);

// Optional client data (validated via spatie/laravel-data)
$client = ClientData::make([
    'name' => 'John Doe',
    'vat' => 'PT123456789',
]);

$invoice->client($client);

$result = $invoice->execute();

// Save the PDF if available
if ($result->output) {
    $result->output->save('invoices/' . $result->output->fileName());
}
```

> Prefer the `Invoice` facade for day-to-day usage. Resolve
> `CsarCrr\InvoicingIntegration\InvoiceAction` from the container only when you
> need to inject the action class directly (for example, in queued jobs or
> service constructors).

> Because `ClientData`, `PaymentData`, and other value objects extend
> `spatie/laravel-data\Data`, always instantiate them via `::make([...])` (or
> dependency injection) so transformers, defaults, and validation attributes are
> applied consistently.

## What's New

**January 2025** - Documentation updated to reflect the new fluent API. Key changes:

- Entry point is now `Invoice::create()` returning a `CreateInvoice` contract
- Methods use plain verbs: `client()`, `item()`, `payment()`, `type()`, `notes()`, `dueDate()`
- Payments are **required** for FR, FS, RG, and NC document types
- Output format selection via `outputFormat()` method
- Enhanced validation for transport details, credit notes, and client data

---

For more details, refer to the sidebar or the individual documentation files.
