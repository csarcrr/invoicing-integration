# Creating a NC Invoice

This guide explains how to create a NC (Nota de Crédito) invoice in the invoicing integration system.

## Quick Example

Below is a complete example for creating a NC (Credit Note) for an invoice:

```php
$item = new InvoiceItem(reference: 'reference-1');
$item->setPrice(500);
$item->setRelatedDocument(documentNumber: 'FT 01P2025/1', lineNumber: 1);

$payment = new InvoicePayment;
$payment->setAmount(500);
$payment->setMethod(DocumentPaymentMethod::MB);

$integration = Invoice::create();
$integration->setType(DocumentType::CreditNote);
$integration->addItem($item);
$integration->addPayment($item);

$integration->setCreditNoteReason('Product returned by customer');

$integration->invoice();

```

The ``line_number`` is the line position in the original invoice.