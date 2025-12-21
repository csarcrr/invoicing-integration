

# Creating an Invoice

Easily create and send an invoice using the Invoicing Integration package. All you need is to set up the integration, add at least one item and one payment, and (optionally) set client details if not invoicing a final consumer.

Below is a complete example for creating a typical invoice:

```php
use CsarCrr\InvoicingIntegration\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

// Create the integration instance
$integration = Invoice::create();

// (Optional) Set client details. Skip this for final consumer invoices.
$client = new Client(vat: '123456789', name: 'John Doe');
$integration->setClient($client);

// Add an item to the invoice
$item = new Item(reference: 'SKU-001', quantity: 2);
$item->setPrice(1000); // price in cents
$item->setDescription('Product Description');
$integration->addItem($item);

// Generate and send the invoice
$invoiceData = $integration->invoice();
```

If you want to change the invoice type you should do:
```php
use CsarCrr\InvoicingIntegration\Enums\DocumentType;

$integration = Invoice::create();
$integration->setType(DocumentType::InvoiceReceipt);
```

Adding a payment:
```php
use CsarCrr\InvoicingIntegration\Enums\DocumentType;

$integration = Invoice::create();

// Add payment details
$payment = new Payment(DocumentPaymentMethod::MONEY, 2000); // Amount in cents
$integration->addPayment($payment);
```

Payments are only required in certain types of documents. Make sure you are fully aware of what types require it or not.

Also, check the <b>configuration</b> for your provider in order to map the invoicing payments to the payments in your provider.

---

You can also configure more parameters of the document. Check the [API Reference](/api-reference?id=invoicingintegration) to figure out the simpler ones not shown in here.

---

**Tips:**
- For final consumer invoices, do **not** set any client (do not call `setClient`).
- You must always add at least one item.