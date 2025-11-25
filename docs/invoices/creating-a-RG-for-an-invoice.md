# Creating a RG (Receipt) for an Invoice

This guide explains how to create a "RG" (Recibo/Receipt) document for an invoice using the Invoicing Integration package.

## What is an RG?

An RG (Recibo) is a receipt document that acknowledges payment for a previously issued invoice. It is commonly used in Portuguese invoicing systems to confirm that an invoice has been paid.

## Steps to Create an RG for an Invoice

1. **Ensure You Have the Invoice Reference**

    - You need the reference or identifier of the invoice for which you want to issue a receipt.

2. **Initialize the InvoicingIntegration**

    ```php
    use CsarCrr\InvoicingIntegration\InvoicingIntegration;

    $integration = Invoice::create();
    ```

3. **Set the Document Type to RG**

    ```php
    use CsarCrr\InvoicingIntegration\Enums\DocumentType;

    $integration->setType(DocumentType::Receipt);
    ```

4. **Add the Related Invoice**

    ```php
    $integration->addRelatedDocument($invoiceReference); // $invoiceReference is the original invoice's identifier
    ```

5. **Add Payment Details**

    ```php
    use CsarCrr\InvoicingIntegration\InvoicePayment;
    use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

    $payment = new InvoicePayment(DocumentPaymentMethod::MONEY, 10000); // Amount in cents
    $integration->addPayment($payment);
    ```

6. **Generate and Send the RG**
    ```php
    $integration->invoice();
    ```

## Example

```php
use CsarCrr\InvoicingIntegration\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

$integration = Invoice::create();
$integration->setType(DocumentType::Receipt);
$integration->addRelatedDocument('INV-2025-001');
$integration->addPayment(new InvoicePayment(DocumentPaymentMethod::MONEY, 10000));
$integration->invoice();
```

---

**Note:**

-   The RG document does not require items or client details, only the related invoice and payment(s).
-   Make sure your provider supports RG/Receipt documents.
