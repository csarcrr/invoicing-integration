# Invoicing Integration Documentation

This package provides a unified, fluent API for integrating with invoicing systems in Portugal, making it easy to issue fiscally compliant documents from your Laravel application.

**Supported Providers:** Cegid Vendus

## Important Legal Disclaimer

**This package facilitates invoicing provider API usage and is not intended to serve as legal guidance.**

It is **your responsibility** to:

- Comply with all applicable invoicing laws and regulations in your jurisdiction
- Understand each provider's specific requirements
- Ensure proper invoicing practices according to your legal obligations
- Validate that your usage complies with tax laws and accounting standards

Always consult with legal and accounting professionals when implementing invoicing solutions.

## What You'll Learn

- **Getting Started** - Install, configure, and issue your first invoice in minutes
- **Invoices** - Create different document types (FT, FR, FS, RG, NC)
- **Clients** - Manage customer records in your invoicing provider
- **Output Formats** - Save invoices as PDFs or print them on thermal printers

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

Issue an invoice-receipt (FR):

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoiceData = InvoiceData::make([
    'type' => InvoiceType::InvoiceReceipt,
    'client' => ClientData::make([
        'name' => 'Pedro Santos',
        'vat' => 'PT234567890',
        'email' => 'pedro.santos@email.pt',
        'address' => 'Avenida da Liberdade, 150',
        'city' => 'Lisboa',
        'postalCode' => '1250-096',
        'country' => 'PT',
    ]),
    'items' => [
        ItemData::make([
            'reference' => 'LAPTOP-PRO-15',
            'note' => 'Professional Laptop 15" - 16GB RAM',
            'price' => 129900, // in cents
            'quantity' => 1,
        ]),
        ItemData::make([
            'reference' => 'SHIPPING-EXPRESS',
            'note' => 'Express Delivery (next business day)',
            'price' => 999,
        ]),
    ],
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::CREDIT_CARD,
            'amount' => 130899,
        ]),
    ],
]);

$result = Invoice::create($invoiceData)->execute()->getInvoice();

if ($result->output) {
    $result->output->save('invoices/' . $result->output->fileName());
}
```

Sample response:

```json
{
    "id": 12345,
    "sequence": "FR 01P2025/1",
    "total": 130899,
    "totalNet": 106422
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

**February 2026** - Invoice creation is now driven by `InvoiceData`. Highlights:

- `Invoice::create(InvoiceData $invoice)` is the canonical entry point
- All tests and docs rely on DTO-first inputs for consistency
- Builder helpers still exist for incremental adjustments but mutate the same DTO instance
- Payments remain **required** for FR, FS, RG, and NC document types
- Responses now hydrate `OutputData`, ensuring PDFs/ESC-POS files expose helper methods consistently

---

Ready to get started? Head to [Getting Started](getting-started.md) for installation instructions.
