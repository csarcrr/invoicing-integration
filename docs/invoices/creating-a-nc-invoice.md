# Creating a Credit Note (NC)

Issue a credit note (NC / Nota de Crédito) to refund or correct a previously issued invoice.

## Quick Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Data\PaymentData;use CsarCrr\InvoicingIntegration\Data\RelatedDocumentReferenceData;use CsarCrr\InvoicingIntegration\Enums\InvoiceType;use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

// Set document type to Credit Note
$invoice->type(InvoiceType::CreditNote);

// Assign the customer (if needed)
$invoice->client(ClientData::make([
    'name' => 'John Doe',
    'vat' => 'PT123456789',
]));

// Create item with reference to original invoice line
$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 500, // Amount to credit in cents
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FT 01P2025/1',
        'row' => 1,
    ]), // Original invoice sequence, line number
]);

$invoice->item($item);

// Add payment (required for NC)
$payment = PaymentData::make([
    'method' => PaymentMethod::MB,
    'amount' => 500,
]);
$invoice->payment($payment);

// Provide reason for credit note (required)
$invoice->creditNoteReason('Product returned by customer');

// Issue the credit note
$result = $invoice->execute();
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
$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 500,
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FT 01P2025/1',
        'row' => 1,
    ]),
]);
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
use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Data\PaymentData;use CsarCrr\InvoicingIntegration\Data\RelatedDocumentReferenceData;use CsarCrr\InvoicingIntegration\Enums\InvoiceType;use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

// Configure as credit note
$invoice->type(InvoiceType::CreditNote);

// Create the item being credited
$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 1500,  // Credit amount in cents (15.00)
    'quantity' => 1,
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FT 01P2025/1',
        'row' => 1,
    ]),
]);

$invoice->item($item);

// Add payment
$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 1500,
]);
$invoice->payment($payment);

// Link to original invoice
$invoice->relatedDocument('FT 01P2025/1', 1);

// Reason for the credit note
$invoice->creditNoteReason('Product damaged during shipping');

// Issue
$result = $invoice->execute();

// Save credit note PDF if provided by the provider
if ($result->output) {
    $result->output->save('credit-notes/' . $result->output->fileName());
}
```

Example credit-note payload:

```json
{
    "id": 222,
    "sequence": "NC 01P2025/1",
    "total": -1500,
    "totalNet": -1219,
    "atcudHash": "NC 01P2025/1 XYZ999"
}
```

## Multiple Items

Credit multiple items from the same invoice:

```php
$invoice = Invoice::create();
$invoice->type(InvoiceType::CreditNote);

// First item (line 1 of original invoice)
$item1 = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 500,
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FT 01P2025/1',
        'row' => 1,
    ]),
]);
$invoice->item($item1);

// Second item (line 2 of original invoice)
$item2 = ItemData::make([
    'reference' => 'SKU-002',
    'price' => 300,
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FT 01P2025/1',
        'row' => 2,
    ]),
]);
$invoice->item($item2);

$payment = PaymentData::make([
    'method' => PaymentMethod::MB,
    'amount' => 800,
]);
$invoice->payment($payment);
$invoice->relatedDocument('FT 01P2025/1', 1);
$invoice->creditNoteReason('Order cancelled by customer');

$result = $invoice->execute();
```

---

**Tips:**

- Always provide a clear, descriptive reason for the credit note
- Each item must reference the original invoice line number
- The payment amount should match the total credit amount
- Credit notes require the same payment method configuration as invoices

---

Next: [Output Formats](outputting-invoice.md)
