
# Creating a RG (Receipt) for an Invoice

Quickly issue a receipt (RG/Recibo) for a previously created invoice using the Invoicing Integration package. RG documents confirm payment for an invoice and are standard in Portuguese invoicing systems.

## Quick Example

Below is a complete example for creating a RG (receipt) for an invoice:

```php
use CsarCrr\InvoicingIntegration\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use CsarCrr\InvoicingIntegration\Enums\InvoicePaymentMethod;

// Create the integration instance
$invoice = Invoice::create();

// Set the document type to RG/Receipt
$invoice->setType(InvoiceType::Receipt);

// Add the related invoice reference (provider identifier)
$invoice->addRelatedDocument('FT 01P2025/1');

// Add payment details
$invoice->addPayment(new Payment(InvoicePaymentMethod::MONEY, 10000)); // Amount in cents

// Generate and send the RG
$invoice->execute();
```

You can also have multiple types of payments assigned. Just make sure that the amounts sum, are the total, or the original issued invoice will not be "marked" as paid in most providers.

```php
// ...

$invoice = Invoice::create();

$invoice->addPayment(new Payment(InvoicePaymentMethod::MONEY, 5000)); 
$invoice->addPayment(new Payment(InvoicePaymentMethod::MB, 5000)); 

$invoice->execute();
```

---

**Tips:**
- RG documents do **not** require items or client details—just the related invoice and payment(s).
- Make sure your provider supports RG/Receipt documents.
