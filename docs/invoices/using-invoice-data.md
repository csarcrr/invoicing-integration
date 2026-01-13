# Using Invoice Data

When you issue an invoice, you receive an `Invoice` value object containing the response data. This object provides a unified interface regardless of which provider you use.

## Accessing Invoice Data

```php
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

$invoice = Invoice::create();
$item = new Item();
$item->reference('SKU-001');
$invoice->item($item);

$result = $invoice->execute();

// Get the invoice sequence number (provider's reference)
$sequence = $result->getSequence(); // e.g., "FT 01P2025/1"

// Get the internal ID (provider's database ID)
$id = $result->getId(); // e.g., 12345678

// Get the output object (PDF or ESC/POS data)
$output = $result->getOutput();
```

## Available Methods

| Method          | Return Type | Description                                        |
| --------------- | ----------- | -------------------------------------------------- |
| `getSequence()` | `string`    | The invoice sequence number (e.g., "FT 01P2025/1") |
| `getId()`       | `int`       | The provider's internal ID                         |
| `getOutput()`   | `Output`    | The output object for accessing PDF/ESC/POS data   |

## Working with the Sequence

The sequence number is the official invoice identifier used in Portugal:

```php
$result = $invoice->execute();

$sequence = $result->getSequence();
// Format: "{TYPE} {SERIES}/{NUMBER}"
// Examples: "FT 01P2025/1", "FR 01P2025/5", "NC 01P2025/2"

// Store for your records
$yourInvoiceRecord->provider_sequence = $sequence;
$yourInvoiceRecord->save();
```

## Working with the ID

The provider's internal ID is useful for API operations like creating receipts or credit notes:

```php
$result = $invoice->execute();

$id = $result->getId();

// Use this ID when creating a receipt for this invoice
$receipt = Invoice::create();
$receipt->type(InvoiceType::Receipt);
$receipt->relatedDocument($id);
// ...
```

## Working with Output

The output object provides access to the generated document:

```php
$result = $invoice->execute();
$output = $result->getOutput();

$path = $output->save(); // saves under "invoices/ft_01P2026.pdf depending on the sequence format of each provider

// Or save with a custom name
$path = $output->save('invoices/custom-invoice-name.pdf');
```

> **Note:** If the provider doesn't return output data, `getOutput()` throws an `InvoiceWithoutOutputException`. See [Output Formats](outputting-invoice.md#handling-missing-output) for handling this case.

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

// Issue an invoice
$invoice = Invoice::create();
$item = new Item();
$item->reference('SKU-001');
$item->price(1000);
$invoice->item($item);

$payment = new Payment();
$payment->method(PaymentMethod::CREDIT_CARD);
$payment->amount(1000);
$invoice->payment($payment);

$result = $invoice->execute();

// Log the result
logger()->info('Invoice issued', [
    'sequence' => $result->getSequence(),
    'id' => $result->getId(),
]);

// Save to your database
$invoiceRecord = new YourInvoiceModel();
$invoiceRecord->provider_id = $result->getId();
$invoiceRecord->sequence = $result->getSequence();
$invoiceRecord->save();

// Save the PDF
$pdfPath = $result->getOutput()->save(
    'invoices/' . $result->getOutput()->fileName()
);

$invoiceRecord->pdf_path = $pdfPath;
$invoiceRecord->save();

// Return to user
return response()->json([
    'success' => true,
    'invoice_number' => $result->getSequence(),
    'pdf_url' => Storage::url($pdfPath),
]);
```

## Provider-Agnostic Code

The `Invoice` value object ensures your code works with any provider:

```php
function processInvoiceResult($result): array
{
    // This works regardless of which provider was used
    return [
        'sequence' => $result->getSequence(),
        'id' => $result->getId(),
        'filename' => $result->getOutput()->fileName(),
    ];
}
```

---

**Tips:**

- Always store both `sequence` and `id` in your database
- Use `sequence` for display and official references
- Use `id` when making related API calls (receipts, credit notes)
- The output object handles both PDF and ESC/POS formats transparently

---

Back to: [API Reference](../api-reference.md)
