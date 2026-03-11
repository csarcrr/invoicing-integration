# Invoices

```php
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$result = Invoice::create($invoiceData)->execute()->getInvoice();
```

## Choosing a Document Type

Portuguese fiscal invoicing uses different document types depending on when the customer pays and why the document is being issued.

| Type | Code | When to Use |
|---|---|---|
| `InvoiceType::Invoice` | FT | Customer pays later — you'll issue a receipt (RG) when they do |
| `InvoiceType::InvoiceReceipt` | FR | Customer pays now — the most common type for immediate sales |
| `InvoiceType::InvoiceSimple` | FS | Quick invoice for small amounts (under 1000), requires payment |
| `InvoiceType::Receipt` | RG | Confirming payment for a previously issued FT |
| `InvoiceType::CreditNote` | NC | Refunding money — returns, corrections, cancellations |
| `InvoiceType::Transport` | GT | Transport/shipping documentation for goods in transit |

### How to decide

Most sales use **FR** (Invoice-Receipt). Use this when the customer pays at the point of sale — it combines the invoice and payment confirmation in a single document.

Use **FT** (Invoice) when payment is deferred (e.g., NET30 terms with a business client). When they eventually pay, issue an **RG** (Receipt) referencing the original FT.

Use **NC** (Credit Note) when you need to refund. Every credit note references the original invoice and requires a reason.

```php
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;

// Immediate sale — most common
$invoiceData = InvoiceData::make([
    'type' => InvoiceType::InvoiceReceipt,
    // ...
]);

// Deferred payment — issue receipt later
$invoiceData = InvoiceData::make([
    'type' => InvoiceType::Invoice,
    'dueDate' => Carbon::now()->addDays(30),
    // ...
]);
```

## Guides

- [Creating an Invoice](creating-an-invoice.md) — Full invoices with clients, items, discounts, and payments
- [Creating a Receipt (RG)](creating-a-RG-for-an-invoice.md) — Confirming payment for a deferred invoice
- [Creating a Credit Note (NC)](creating-a-nc-invoice.md) — Handling refunds and returns
- [Tax Exemptions](tax-exemption.md) — Configuring VAT exemptions with the correct M-codes
- [Output Formats](outputting-invoice.md) — Saving PDFs or printing to thermal printers
- [Using Invoice Data](using-invoice-data.md) — Working with the response after issuing
