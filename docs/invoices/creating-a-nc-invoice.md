# Creating a Credit Note (NC)

Issue a credit note (NC / Nota de Crédito) to refund or correct a previously issued invoice.

## Quick Example

```php
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

$invoice = Invoice::create();

// Set document type to Credit Note
$invoice->type(InvoiceType::CreditNote);

// Create item with reference to original invoice line
$item = new Item();
$item->reference('SKU-001');
$item->price(500); // Amount to credit in cents
$item->relatedDocument('FT 01P2025/1', 1); // Original invoice sequence, line number

$invoice->item($item);

// Add payment (required for NC)
$payment = new Payment();
$payment->method(PaymentMethod::MB);
$payment->amount(500);
$invoice->payment($payment);

// Provide reason for credit note (required)
$invoice->creditNoteReason('Product returned by customer');

// Issue the credit note
$result = $invoice->invoice();
```

## Requirements

Credit notes have specific requirements that differ from regular invoices:

| Requirement           | Notes                                              |
| --------------------- | -------------------------------------------------- |
| Document Type         | Must be `InvoiceType::CreditNote`                  |
| Credit Note Reason    | **Required** - explanation for the credit          |
| Related Document      | Required on both invoice and item level            |
| Item Related Document | Each item must reference the original invoice line |
| Payment               | **Required** for credit notes                      |

## Item-Level Related Document

Each item in a credit note must reference the original invoice and line number:

```php
$item = new Item();
$item->reference('SKU-001');
$item->price(500);
$item->relatedDocument('FT 01P2025/1', 1); // Document sequence, line number
```

The `lineNumber` is the row position in the original invoice:

- If the item was on the 1st line of the original invoice, use `1`
- If it was on the 3rd line, use `3`

> **Note:** This is required for maintaining abstraction across providers, even if some providers don't strictly require it.

## Credit Note Reason

The reason is **mandatory** for credit notes:

```php
$invoice->creditNoteReason('Product damaged');
$invoice->creditNoteReason('Customer returned item');
$invoice->creditNoteReason('Pricing error correction');
```

If you attempt to issue a credit note without a reason, a `CreditNoteReasonIsMissingException` is thrown.

> **Note:** If you call `creditNoteReason()` on a non-NC document type, it is silently ignored.

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

$invoice = Invoice::create();

// Configure as credit note
$invoice->type(InvoiceType::CreditNote);

// Create the item being credited
$item = new Item();
$item->reference('SKU-001');
$item->price(1500);  // Credit amount in cents (15.00)
$item->quantity(1);
$item->relatedDocument('FT 01P2025/1', 1);

$invoice->item($item);

// Add payment
$payment = new Payment();
$payment->method(PaymentMethod::CREDIT_CARD);
$payment->amount(1500);
$invoice->payment($payment);

// Link to original invoice
$invoice->relatedDocument('FT 01P2025/1', 1);

// Reason for the credit note
$invoice->creditNoteReason('Product damaged during shipping');

// Issue
$result = $invoice->invoice();

echo $result->getSequence(); // e.g., "NC 01P2025/1"

// Save credit note PDF
$result->getOutput()->save('credit-notes/' . $result->getOutput()->fileName());
```

## Multiple Items

Credit multiple items from the same invoice:

```php
$invoice = Invoice::create();
$invoice->type(InvoiceType::CreditNote);

// First item (line 1 of original invoice)
$item1 = new Item();
$item1->reference('SKU-001');
$item1->price(500);
$item1->relatedDocument('FT 01P2025/1', 1);
$invoice->item($item1);

// Second item (line 2 of original invoice)
$item2 = new Item();
$item2->reference('SKU-002');
$item2->price(300);
$item2->relatedDocument('FT 01P2025/1', 2);
$invoice->item($item2);

$payment = new Payment();
$payment->method(PaymentMethod::MB);
$payment->amount(800);
$invoice->payment($payment);
$invoice->relatedDocument('FT 01P2025/1', 1);
$invoice->creditNoteReason('Order cancelled by customer');

$result = $invoice->invoice();
```

---

**Tips:**

- Always provide a clear, descriptive reason for the credit note
- Each item must reference the original invoice line number
- The payment amount should match the total credit amount
- Credit notes require the same payment method configuration as invoices

---

Next: [Output Formats](outputting-invoice.md)
