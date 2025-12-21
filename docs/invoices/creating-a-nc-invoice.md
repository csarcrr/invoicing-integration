# Creating a NC Invoice

This guide explains how to create a NC (Nota de Crédito) invoice in the invoicing integration system.

## Quick Example

Below is a complete example for creating a NC (Credit Note) for an invoice:

```php
$item = new Item(reference: 'reference-1');
$item->setPrice(500);
$item->setRelatedDocument(documentNumber: 'FT 01P2025/1', lineNumber: 1);

$payment = new Payment;
$payment->setAmount(500);
$payment->setMethod(DocumentPaymentMethod::MB);

$integration = Invoice::create();
$integration->setType(DocumentType::CreditNote);
$integration->addItem($item);
$integration->addPayment($item);

$integration->setCreditNoteReason('Product returned by customer');

$integration->invoice();

```

The ``lineNumber`` is the row position in the original invoice. So if the item on the invoice was issued in the 3rd row, the ``lineNumber`` should be ``3``. This might not be a requirement for all integrations, but in order to maintain abstraction we will require it always.

