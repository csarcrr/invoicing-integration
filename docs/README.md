# Invoicing Integration Documentation

A unified, fluent API for integrating with Portuguese invoicing systems from your Laravel application. Currently supports **Cegid Vendus**.

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
        'vat' => '234567890',
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
    $result->output->save('invoices/' . $result->output->fileName);
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

## Important Legal Disclaimer

This package facilitates invoicing provider API usage and is not intended to serve as legal guidance. It is your responsibility to comply with all applicable invoicing laws and regulations, understand each provider's specific requirements, and validate that your usage complies with tax laws and accounting standards. Always consult with legal and accounting professionals when implementing invoicing solutions.

## Table of Contents

- [Getting Started](getting-started.md) - Installation and configuration
- [Features](features.md) - Supported features by provider and document type
- [Handling Errors](handling-errors.md)
- [Changelog](changelog.md)

### Items

- [Overview](items/README.md)
- [Creating an Item](items/creating-an-item.md)
- [Updating an Item](items/updating-an-item.md)
- [Deleting an Item](items/deleting-an-item.md)
- [Getting an Item](items/getting-an-item.md)
- [Finding Items](items/finding-an-item.md)

### Categories

- [Overview](categories/README.md)
- [Creating a Category](categories/creating-a-category.md)
- [Updating a Category](categories/updating-a-category.md)
- [Finding Categories](categories/finding-categories.md)
- [Getting a Category](categories/getting-a-category.md)

### Item Stocks

- [Overview](item-stocks/README.md)
- [Moving Stock](item-stocks/moving-stock.md)

### Clients

- [Overview](clients/README.md)
- [Creating a Client](clients/creating-a-client.md)
- [Updating a Client](clients/updating-a-client.md)
- [Deleting a Client](clients/deleting-a-client.md)
- [Getting a Client](clients/getting-a-client.md)
- [Finding Clients](clients/finding-clients.md)

### Companies

- [Overview](companies/README.md)
- [Finding Companies](companies/finding-companies.md)

### Due Dates

- [Overview](due-dates/README.md)
- [Creating a Due Date](due-dates/creating-a-due-date.md)
- [Finding Due Dates](due-dates/finding-due-dates.md)

### Payment Methods

- [Overview](payment-methods/README.md)
- [Creating a Payment Method](payment-methods/creating-a-payment-method.md)
- [Finding Payment Methods](payment-methods/finding-payment-methods.md)

### Taxes

- [Overview](taxes/README.md)
- [Creating a Tax](taxes/creating-a-tax.md)
- [Finding Taxes](taxes/finding-taxes.md)
- [Finding Tax Exemptions](taxes/finding-tax-exemptions.md)

### Units

- [Overview](units/README.md)
- [Creating a Unit](units/creating-a-unit.md)
- [Finding Units](units/finding-units.md)

### Warehouses

- [Overview](warehouses/README.md)
- [Creating a Warehouse](warehouses/creating-a-warehouse.md)
- [Finding Warehouses](warehouses/finding-warehouses.md)

### Document Types

- [Overview](document-types/README.md)
- [Finding Document Types](document-types/finding-document-types.md)

### Invoices

- [Overview](invoices/README.md)
- [Creating an Invoice](invoices/creating-an-invoice.md)
- [Creating a Receipt (RG)](invoices/creating-a-RG-for-an-invoice.md)
- [Creating a Credit Note (NC)](invoices/creating-a-nc-invoice.md)
- [Getting an Invoice](invoices/getting-an-invoice.md)
- [Finding Invoices](invoices/finding-invoices.md)
- [Cancelling an Invoice](invoices/cancelling-an-invoice.md)
- [Tax Exemptions](invoices/tax-exemption.md)
- [Output Formats](invoices/outputting-invoice.md)
- [Using Invoice Data](invoices/using-invoice-data.md)

### Reference

- [API Reference](api-reference.md)

### Providers

- [Cegid Vendus](providers/cegid-vendus/configuration.md)
- [Moloni](providers/moloni/configuration.md)
- [Invoice Express](providers/invoice-express/configuration.md)

## What's New

For a full history of changes, see the [CHANGELOG](../CHANGELOG.md).

---

Ready to get started? Head to [Getting Started](getting-started.md) for installation instructions.
