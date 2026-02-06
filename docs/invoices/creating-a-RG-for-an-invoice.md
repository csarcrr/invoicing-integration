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
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Create the receipt
$receipt = Invoice::create()
    ->type(InvoiceType::Receipt);

// Link it to the original invoice (use the provider's invoice ID)
$receipt->relatedDocument(99999999);

// Record how they paid
$payment = PaymentData::make([
    'method' => PaymentMethod::MONEY_TRANSFER,
    'amount' => 89999, // 899.99 - full invoice amount
]);
$receipt->payment($payment);

// Issue the receipt
$result = $receipt->execute()->getInvoice();
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
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Step 1: Issue an FT invoice with 30-day payment terms
$invoice = Invoice::create()
    ->type(InvoiceType::Invoice);

$client = ClientData::make([
    'name' => 'TechStore Portugal Lda',
    'vat' => 'PT509876543',
    'email' => 'accounts@techstore.pt',
]);
$invoice->client($client);

$product = ItemData::make([
    'reference' => 'LAPTOP-BULK-ORDER',
    'note' => 'Business Laptop - Bulk Order (10 units)',
    'price' => 89999, // 899.99 each
    'quantity' => 10,
]);
$invoice->item($product);

$invoice->dueDate(Carbon::now()->addDays(30));
$invoice->notes('NET30 payment terms as agreed');

$invoiceResult = $invoice->execute()->getInvoice();

// Store the invoice ID for later
$invoiceId = $invoiceResult->id;
```

```php
// Step 2: 30 days later, the customer pays via bank transfer
$receipt = Invoice::create()
    ->type(InvoiceType::Receipt);

$receipt->relatedDocument($invoiceId);

$payment = PaymentData::make([
    'method' => PaymentMethod::MONEY_TRANSFER,
    'amount' => 899990, // Full order total
]);
$receipt->payment($payment);

$receiptResult = $receipt->execute()->getInvoice();

// Save the receipt
if ($receiptResult->output) {
    $receiptResult->output->save('receipts/' . $receiptResult->output->fileName());
}
```

## Partial Payments

Sometimes customers pay invoices in installments. Issue separate receipts for each payment:

```php
// Customer pays first installment (50%)
$receipt1 = Invoice::create()
    ->type(InvoiceType::Receipt);

$receipt1->relatedDocument($invoiceId);
$receipt1->payment(PaymentData::make([
    'method' => PaymentMethod::MONEY_TRANSFER,
    'amount' => 449995, // First 50%
]));
$receipt1->notes('Installment 1 of 2');

$receipt1->execute()->getInvoice();

// Later: Customer pays final installment
$receipt2 = Invoice::create()
    ->type(InvoiceType::Receipt);

$receipt2->relatedDocument($invoiceId);
$receipt2->payment(PaymentData::make([
    'method' => PaymentMethod::MONEY_TRANSFER,
    'amount' => 449995, // Final 50%
]));
$receipt2->notes('Installment 2 of 2 - Final payment');

$receipt2->execute()->getInvoice();
```

## Split Payment Methods

Customers can pay a single receipt using multiple payment methods:

```php
$receipt = Invoice::create()
    ->type(InvoiceType::Receipt);

$receipt->relatedDocument($invoiceId);

// Part of the payment in cash
$cash = PaymentData::make([
    'method' => PaymentMethod::MONEY,
    'amount' => 50000, // 500.00
]);
$receipt->payment($cash);

// Rest via bank transfer
$transfer = PaymentData::make([
    'method' => PaymentMethod::MONEY_TRANSFER,
    'amount' => 399990, // 3999.90
]);
$receipt->payment($transfer);

$result = $receipt->execute()->getInvoice();
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
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Issue receipt for paid invoice
$receipt = Invoice::create()
    ->type(InvoiceType::Receipt);

// Link to the original invoice
$receipt->relatedDocument(99999999);

// Record the payment method and amount
$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 25499, // 254.99
]);
$receipt->payment($payment);

// Add a thank you note
$receipt->notes('Thank you for your prompt payment!');

// Issue the receipt
$result = $receipt->execute()->getInvoice();

// Save the PDF
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
