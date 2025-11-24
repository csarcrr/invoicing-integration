
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

2. **Set the Client**
	```php
	use CsarCrr\InvoicingIntegration\InvoiceClient;

	$client = new InvoiceClient(vat: 'PT123456789', name: 'John Doe');
	$integration->setClient($client);
	```

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
$client = new InvoiceClient(vat: '123456789', name: 'John Doe');
$integration->setClient($client);

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
- The invoice requires at least one client, one item, and one payment.
- Make sure your provider supports invoice documents and the required fields.

For more details, see the [API Reference](../README.md) and [Providers Configuration](../providers/cegid-vendus/configuration.md).
