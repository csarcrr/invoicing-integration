# Creating a Credit Note (NC)

When a customer returns a product or you need to correct a billing error, you issue a credit note (NC / Nota de Crédito). This document officially refunds money and maintains proper accounting records.

## When to Use Credit Notes

**Use NC when:**

- A customer returns a product (full or partial refund)
- You overcharged a customer and need to correct it
- A product arrived damaged and you're issuing a refund
- You're canceling part of an order after invoicing

**Don't use NC when:**

- The customer hasn't paid yet - void the original invoice instead
- You want to record a payment - use RG (Receipt) instead

## Quick Example: Customer Returns a Product

A customer bought headphones but they arrived defective. Let's issue a credit note:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Data\RelatedDocumentReferenceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$creditNote = Invoice::create()
    ->type(InvoiceType::CreditNote);

// Same customer who made the original purchase
$client = ClientData::make([
    'name' => 'Maria Silva',
    'vat' => 'PT123456789',
]);
$creditNote->client($client);

// The item being refunded (must reference the original invoice line)
$item = ItemData::make([
    'reference' => 'HEADPHONES-PRO',
    'note' => 'Wireless Noise-Cancelling Headphones - Defective unit',
    'price' => 14999, // Refund amount in cents
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FR 01P2025/1', // Original invoice sequence
        'row' => 1, // Line 1 of the original invoice
    ]),
]);
$creditNote->item($item);

// How you're refunding the money
$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 14999, // Refund to original payment method
]);
$creditNote->payment($payment);

// Link to the original invoice
$creditNote->relatedDocument('FR 01P2025/1', 1);

// Explain why you're issuing the credit note (required)
$creditNote->creditNoteReason('Product arrived defective - customer return');

// Issue the credit note
$result = $creditNote->execute()->getInvoice();
```

Sample credit note response:

```json
{
    "id": 222,
    "sequence": "NC 01P2025/1",
    "total": -14999,
    "totalNet": -12194,
    "atcudHash": "NC 01P2025/1 XYZ999"
}
```

## Partial Refunds

Sometimes you only need to refund part of an order. For example, a customer ordered 3 items but wants to return 1:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Data\RelatedDocumentReferenceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Original order had:
// Line 1: Laptop - 1299.00
// Line 2: Mouse - 39.99
// Line 3: Keyboard - 79.99
// Customer returns the mouse

$creditNote = Invoice::create()
    ->type(InvoiceType::CreditNote);

$client = ClientData::make([
    'name' => 'Carlos Ferreira',
    'vat' => 'PT567890123',
]);
$creditNote->client($client);

// Only refunding the mouse (line 2 of the original invoice)
$item = ItemData::make([
    'reference' => 'MOUSE-WIRELESS',
    'note' => 'Ergonomic Wireless Mouse - Customer changed mind',
    'price' => 3999,
    'quantity' => 1,
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FR 01P2025/5',
        'row' => 2, // This was line 2 on the original invoice
    ]),
]);
$creditNote->item($item);

$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 3999,
]);
$creditNote->payment($payment);

$creditNote->relatedDocument('FR 01P2025/5', 2);
$creditNote->creditNoteReason('Customer return - item not needed');

$result = $creditNote->execute()->getInvoice();
```

## Multiple Items in One Credit Note

If a customer returns multiple items from the same order:

```php
$creditNote = Invoice::create()
    ->type(InvoiceType::CreditNote);

$client = ClientData::make([
    'name' => 'Ana Rodrigues',
    'vat' => 'PT987654321',
]);
$creditNote->client($client);

// Return item 1: Monitor (was line 1)
$monitor = ItemData::make([
    'reference' => 'MONITOR-4K-27',
    'note' => '27" 4K IPS Monitor - Dead pixels on arrival',
    'price' => 44999,
    'quantity' => 1,
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FR 01P2025/10',
        'row' => 1,
    ]),
]);
$creditNote->item($monitor);

// Return item 2: Cable (was line 2)
$cable = ItemData::make([
    'reference' => 'CABLE-DP-2M',
    'note' => 'DisplayPort Cable - Not needed without monitor',
    'price' => 1499,
    'quantity' => 1,
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FR 01P2025/10',
        'row' => 2,
    ]),
]);
$creditNote->item($cable);

// Total refund
$payment = PaymentData::make([
    'method' => PaymentMethod::MONEY_TRANSFER,
    'amount' => 46498, // 449.99 + 14.99
]);
$creditNote->payment($payment);

$creditNote->relatedDocument('FR 01P2025/10', 1);
$creditNote->creditNoteReason('Product quality issue - full return');

$result = $creditNote->execute()->getInvoice();
```

## Requirements

Credit notes have specific requirements that differ from regular invoices:

| Requirement           | Notes                                              |
| --------------------- | -------------------------------------------------- |
| Document Type         | Must be `InvoiceType::CreditNote`                  |
| Credit Note Reason    | **Required** - explains why you're issuing it      |
| Related Document      | Required - reference to original invoice           |
| Item Related Document | Each item must reference its original invoice line |
| Payment               | **Required** - how you're refunding the money      |

## Credit Note Reasons

The reason is **mandatory** and should clearly explain the refund:

```php
// Common reasons
$creditNote->creditNoteReason('Product arrived damaged');
$creditNote->creditNoteReason('Customer return - 30 day policy');
$creditNote->creditNoteReason('Incorrect product shipped');
$creditNote->creditNoteReason('Price adjustment - promotional discount');
$creditNote->creditNoteReason('Order cancellation before shipping');
```

If you try to issue a credit note without a reason, you'll get a `CreditNoteReasonIsMissingException`.

## Understanding Line Numbers

The `row` in `relatedDocument` refers to the position of the item in the original invoice:

```php
// If the original invoice looked like this:
// Line 1: Laptop - 1299.00
// Line 2: Mouse - 39.99  <-- You want to refund this
// Line 3: Keyboard - 79.99

// Reference line 2
$item = ItemData::make([
    // ...
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FR 01P2025/1',
        'row' => 2, // Mouse was on line 2
    ]),
]);
```

> **Note:** This is required for maintaining compatibility across providers, even if some providers don't strictly require it.

## Complete Example

Full credit note example:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Data\RelatedDocumentReferenceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$creditNote = Invoice::create()
    ->type(InvoiceType::CreditNote);

// Same customer from the original order
$client = ClientData::make([
    'name' => 'Pedro Santos',
    'vat' => 'PT234567890',
    'email' => 'pedro.santos@email.pt',
]);
$creditNote->client($client);

// The laptop being returned
$item = ItemData::make([
    'reference' => 'LAPTOP-ULTRA-13',
    'note' => 'UltraBook Pro 13" - Customer return (14-day policy)',
    'price' => 129900,
    'quantity' => 1,
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FR 01P2025/25',
        'row' => 1,
    ]),
]);
$creditNote->item($item);

// Refund to original payment method
$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 129900,
]);
$creditNote->payment($payment);

// Link to original invoice
$creditNote->relatedDocument('FR 01P2025/25', 1);

// Clear explanation for accounting
$creditNote->creditNoteReason('Customer return within 14-day cooling-off period');

// Issue the credit note
$result = $creditNote->execute()->getInvoice();

// Save the credit note PDF
if ($result->output) {
    $result->output->save('credit-notes/' . $result->output->fileName());
}
```

Example credit note response:

```json
{
    "id": 333,
    "sequence": "NC 01P2025/1",
    "total": -129900,
    "totalNet": -105610,
    "atcudHash": "NC 01P2025/1 XYZ999"
}
```

---

**Tips:**

- Always provide a clear, descriptive reason for the credit note
- Each item must reference its line number on the original invoice
- The payment amount should match the total credit amount
- Credit notes require the same payment method configuration as invoices
- The `total` in the response will be negative, indicating a refund

---

Next: [Output Formats](outputting-invoice.md)
