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
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create()
    ->type(InvoiceType::InvoiceReceipt);

// Add products
$product = ItemData::make([
    'reference' => 'LAPTOP-PRO-15',
    'note' => 'Professional Laptop 15" - 16GB RAM',
    'price' => 129900, // 1299.00 in cents
    'quantity' => 1,
]);
$invoice->item($product);

$shipping = ItemData::make([
    'reference' => 'SHIPPING-EXPRESS',
    'note' => 'Express Delivery (next business day)',
    'price' => 999, // 9.99 in cents
]);
$invoice->item($shipping);

// Add payment
$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 130899, // Total: 1308.99
]);
$invoice->payment($payment);

// Add customer details
$client = ClientData::make([
    'name' => 'Pedro Santos',
    'vat' => 'PT234567890',
    'email' => 'pedro.santos@email.pt',
    'address' => 'Avenida da Liberdade, 150',
    'city' => 'Lisboa',
    'postalCode' => '1250-096',
    'country' => 'PT',
]);
$invoice->client($client);

// Issue the invoice
$result = $invoice->execute()->getInvoice();

// Save the PDF
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

**January 2025** - Documentation updated to reflect the new fluent API. Key changes:

- Entry point is now `Invoice::create()` returning a `CreateInvoice` contract
- Methods use plain verbs: `client()`, `item()`, `payment()`, `type()`, `notes()`, `dueDate()`
- Payments are **required** for FR, FS, RG, and NC document types
- Output format selection via `outputFormat()` method
- Enhanced validation for transport details, credit notes, and client data

---

Ready to get started? Head to [Getting Started](getting-started.md) for installation instructions.
