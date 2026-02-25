# Creating a Receipt (RG) for an Invoice

When you issue an FT invoice (deferred payment), the customer pays later. Once they pay, you need to issue a receipt (RG/Recibo) to confirm the payment. This is standard practice in Portuguese invoicing systems.

## When to Use Receipts

**Use RG when:**

- You previously issued an FT invoice with a due date
- The customer has now paid that invoice
- You need to provide proof of payment

**Don't use RG when:**

- The customer paid at the time of purchase - use FR (Invoice-Receipt) instead
- You're refunding money - use NC (Credit Note) instead

## Quick Example

A customer just paid their outstanding invoice. Let's issue the receipt:

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$receiptData = InvoiceData::make([
    'type' => InvoiceType::Receipt,
    'relatedDocument' => 99999999,
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::MONEY_TRANSFER,
            'amount' => 89999,
        ]),
    ],
]);

$result = Invoice::create($receiptData)->execute()->getInvoice();
```

Sample receipt response:

```json
{
    "id": 789,
    "sequence": "RG 01P2025/1",
    "total": 89999,
    "totalNet": 73170
}
```

## The Invoice-to-Receipt Flow

Here's a complete example from invoice to receipt:

```php
use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoiceData = InvoiceData::make([
    'type' => InvoiceType::Invoice,
    'client' => ClientData::make([
        'name' => 'TechStore Portugal Lda',
        'vat' => 'PT509876543',
        'email' => 'accounts@techstore.pt',
    ]),
    'items' => [
        ItemData::make([
            'reference' => 'LAPTOP-BULK-ORDER',
            'note' => 'Business Laptop - Bulk Order (10 units)',
            'price' => 89999,
            'quantity' => 10,
        ]),
    ],
    'dueDate' => Carbon::now()->addDays(30),
    'notes' => 'NET30 payment terms as agreed',
]);

$invoiceResult = Invoice::create($invoiceData)->execute()->getInvoice();
$invoiceId = $invoiceResult->id;
```

```php
// Step 2: 30 days later, the customer pays via bank transfer
$receiptData = InvoiceData::make([
    'type' => InvoiceType::Receipt,
    'relatedDocument' => $invoiceId,
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::MONEY_TRANSFER,
            'amount' => 899990,
        ]),
    ],
]);

$receiptResult = Invoice::create($receiptData)->execute()->getInvoice();

if ($receiptResult->output) {
    $receiptResult->output->save('receipts/' . $receiptResult->output->fileName());
}
```

## Partial Payments

Sometimes customers pay invoices in installments. Issue separate receipts for each payment:

```php
$receipt1 = InvoiceData::make([
    'type' => InvoiceType::Receipt,
    'relatedDocument' => $invoiceId,
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::MONEY_TRANSFER,
            'amount' => 449995,
        ]),
    ],
    'notes' => 'Installment 1 of 2',
]);
Invoice::create($receipt1)->execute();

$receipt2 = InvoiceData::make([
    'type' => InvoiceType::Receipt,
    'relatedDocument' => $invoiceId,
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::MONEY_TRANSFER,
            'amount' => 449995,
        ]),
    ],
    'notes' => 'Installment 2 of 2 - Final payment',
]);
Invoice::create($receipt2)->execute();
```

## Split Payment Methods

Customers can pay a single receipt using multiple payment methods:

```php
$receiptData = InvoiceData::make([
    'type' => InvoiceType::Receipt,
    'relatedDocument' => $invoiceId,
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::MONEY,
            'amount' => 50000,
        ]),
        PaymentData::make([
            'method' => PaymentMethod::MONEY_TRANSFER,
            'amount' => 399990,
        ]),
    ],
]);

$result = Invoice::create($receiptData)->execute()->getInvoice();
```

## Requirements

| Requirement      | Notes                                         |
| ---------------- | --------------------------------------------- |
| Document Type    | Must be `InvoiceType::Receipt`                |
| Related Document | Required - the original invoice's provider ID |
| Payment          | Required - at least one payment               |
| Items            | **Not required** for receipts                 |
| Client           | **Not required** for receipts                 |

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$receiptData = InvoiceData::make([
    'type' => InvoiceType::Receipt,
    'relatedDocument' => 99999999,
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::CREDIT_CARD,
            'amount' => 25499,
        ]),
    ],
    'notes' => 'Thank you for your prompt payment!',
]);

$result = Invoice::create($receiptData)->execute()->getInvoice();

if ($result->output) {
    $result->output->save('receipts/' . $result->output->fileName());
}
```

---

**Tips:**

- RG documents require the original invoice reference and payment(s)
- No items or client details are needed for receipts
- Ensure payment method IDs are configured in your provider settings
- For full payment, the total should match the original invoice amount
- For partial payments, issue separate receipts for each installment

---

Next: [Creating a Credit Note (NC)](creating-a-nc-invoice.md)
