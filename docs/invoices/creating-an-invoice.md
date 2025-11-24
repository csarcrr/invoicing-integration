
# Creating an Invoice

This guide explains how to create an invoice document using the Invoicing Integration package.

## What is an Invoice?
An invoice is a document that records the sale of goods or services, including client, item, and payment details. In Portuguese invoicing systems, it is a fiscal document required for most business transactions.

## Steps to Create an Invoice

1. **Initialize the InvoicingIntegration**
	```php
	use CsarCrr\InvoicingIntegration\InvoicingIntegration;

	$integration = Invoice::create(); // or your configured provider
	```


2. **Set the Client (Optional for Final Consumer)**
	```php
	use CsarCrr\InvoicingIntegration\InvoiceClient;

	// For regular clients:
	$client = new InvoiceClient(vat: '123456789', name: 'John Doe');
	$integration->setClient($client);
	```
	> **Final Consumer:** If you are invoicing to a final consumer, simply do not set any client information (do not call `setClient`). The invoice will be issued without client details.

3. **Add Items**
	```php
	use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

	$item = new InvoiceItem(reference: 'SKU-001', quantity: 2);
	$item->setPrice(1000); // price in cents
	$item->setDescription('Product Description');
	$integration->addItem($item);
	```

4. **Add Payment Details**
	```php
	use CsarCrr\InvoicingIntegration\InvoicePayment;
	use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

	$payment = new InvoicePayment(DocumentPaymentMethod::MONEY, 2000); // Amount in cents
	$integration->addPayment($payment);
	```

5. **Generate and Send the Invoice**
	```php
	$invoiceData = $integration->invoice();
	```

## Example
```php
use CsarCrr\InvoicingIntegration\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

$integration = Invoice::create();

// For regular clients:
$client = new InvoiceClient(vat: '123456789', name: 'John Doe');
$integration->setClient($client);

// For final consumer, do NOT set any client:
// (do not call setClient)

$item = new InvoiceItem(reference: 'SKU-001', quantity: 2);
$item->setPrice(1000);
$item->setDescription('Product Description');
$integration->addItem($item);

$payment = new InvoicePayment(DocumentPaymentMethod::MONEY, 2000);
$integration->addPayment($payment);

$invoiceData = $integration->invoice();
```

---


**Note:**
- For regular invoices, you must set at least one client, one item, and one payment.
- For final consumer invoices, do not set any client information.

For more details, see the [API Reference](../api-reference.md) and [Providers Configuration](../providers/cegid-vendus/configuration.md).
