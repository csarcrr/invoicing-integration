

# Creating an Invoice

Easily create and send an invoice using the Invoicing Integration package. All you need is to set up the integration, add at least one item and one payment, and (optionally) set client details if not invoicing a final consumer.

Below is a complete example for creating a typical invoice:

```php
use CsarCrr\InvoicingIntegration\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

// Create the integration instance
$integration = Invoice::create();

// (Optional) Set client details. Skip this for final consumer invoices.
$client = new InvoiceClient(vat: '123456789', name: 'John Doe');
$integration->setClient($client);

// Add an item to the invoice
$item = new InvoiceItem(reference: 'SKU-001', quantity: 2);
$item->setPrice(1000); // price in cents
$item->setDescription('Product Description');
$integration->addItem($item);

// Add payment details
$payment = new InvoicePayment(DocumentPaymentMethod::MONEY, 2000); // Amount in cents
$integration->addPayment($payment);

// Generate and send the invoice
$invoiceData = $integration->invoice();
```

---

**Tips:**
- For final consumer invoices, do **not** set any client (do not call `setClient`).
- You must always add at least one item and one payment.

For more details, see the [API Reference](../api-reference.md) and [Providers Configuration](../providers/cegid-vendus/configuration.md).
