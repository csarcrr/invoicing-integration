# Creating a Receipt (RG) for an Invoice

Issue a receipt (RG/Recibo) for a previously created invoice. RG documents confirm payment and are standard in Portuguese invoicing systems.

## Quick Example

```php
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

$invoice = Invoice::create();

// Set document type to Receipt
$invoice->type(InvoiceType::Receipt);

// Reference the original invoice (provider identifier or internal ID)
$invoice->relatedDocument(99999999);

// Add payment details (required for RG)
$payment = new Payment();
$payment->method(PaymentMethod::MONEY);
$payment->amount(10000); // 100.00
$invoice->payment($payment);

// Issue the receipt
$result = $invoice->execute();

echo $result->getSequence(); // e.g., "RG 01P2025/1"
```

## Multiple Payment Methods

You can split the payment across multiple methods:

```php
$invoice = Invoice::create();
$invoice->type(InvoiceType::Receipt);
$invoice->relatedDocument(99999999);

// Split payment: 50.00 cash + 50.00 MB
$cash = new Payment();
$cash->method(PaymentMethod::MONEY);
$cash->amount(5000);
$invoice->payment($cash);

$mb = new Payment();
$mb->method(PaymentMethod::MB);
$mb->amount(5000);
$invoice->payment($mb);

$result = $invoice->execute();
```

> **Important:** The total of all payments should match the invoice amount for the original invoice to be marked as "paid" by most providers.

## Requirements

| Requirement      | Notes                                        |
| ---------------- | -------------------------------------------- |
| Document Type    | Must be `InvoiceType::Receipt`               |
| Related Document | Required - reference to the original invoice |
| Payment          | Required - at least one payment              |
| Items            | **Not required** for receipts                |
| Client           | **Not required** for receipts                |

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

$invoice = Invoice::create();

// Configure as receipt
$invoice->type(InvoiceType::Receipt);

// Link to the original invoice
$invoice->relatedDocument(99999999); // Use the provider's invoice ID

// Add payment(s)
$payment = new Payment();
$payment->method(PaymentMethod::CREDIT_CARD);
$payment->amount(15000); // 150.00
$invoice->payment($payment);

// Optional: add notes
$invoice->notes('Payment received. Thank you!');

// Issue
$result = $invoice->execute();

// Save receipt PDF
$result->getOutput()->save('receipts/' . $result->getOutput()->fileName());
```

---

**Tips:**

- RG documents require the related invoice reference and payment(s)
- No items or client details are needed for receipts
- Ensure payment method IDs are configured in your provider settings
- The payment total should match the original invoice amount

---

Next: [Creating a Credit Note (NC)](creating-a-nc-invoice.md)
