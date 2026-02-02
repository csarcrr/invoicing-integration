# Using Invoice Data

When you issue an invoice, you receive an `Invoice` value object containing the response data. This object provides a unified interface regardless of which provider you use.

## Accessing Invoice Data

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();
$item = ItemData::make(['reference' => 'SKU-001']);
$invoice->item($item);

$result = $invoice->execute();

// Get the invoice sequence number (provider's reference)
$sequence = $result->sequence; // e.g., "FT 01P2025/1"

// Get the internal ID (provider's database ID)
$id = $result->id; // e.g., 12345678

// Get the output object (PDF or ESC/POS data, may be null)
$output = $result->output;
```

## Available Methods

| Property   | Type      | Description                                        |
| ---------- | --------- | -------------------------------------------------- |
| `sequence` | `string`  | The invoice sequence number (e.g., "FT 01P2025/1") |
| `id`       | `int`     | The provider's internal ID                         |
| `output`   | `?Output` | Output object, or `null` when no file is provided  |

## Working with the Sequence

The sequence number is the official invoice identifier used in Portugal:

```php
$result = $invoice->execute();

$sequence = $result->sequence;
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

$id = $result->id;

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
$output = $result->output;

if ($output) {
    $path = $output->save(); // saves under "invoices/ft_01P2026.pdf" depending on the provider

    // Or save with a custom name
    $path = $output->save('invoices/custom-invoice-name.pdf');
}
```

> **Note:** `$result->output` is `null` whenever the provider doesn't return files. Handle that case before calling `save()`.

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Data\PaymentData;use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Issue an invoice
$invoice = Invoice::create();
$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 1000,
]);
$invoice->item($item);

$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 1000,
]);
$invoice->payment($payment);

$result = $invoice->execute();

// Log the result
logger()->info('Invoice issued', [
    'sequence' => $result->sequence,
    'id' => $result->id,
]);

// Save to your database
$invoiceRecord = new YourInvoiceModel();
$invoiceRecord->provider_id = $result->id;
$invoiceRecord->sequence = $result->sequence;
$invoiceRecord->save();

// Save the PDF when available
$pdfPath = null;

if ($result->output) {
    $pdfPath = $result->output->save(
        'invoices/' . $result->output->fileName()
    );

    $invoiceRecord->pdf_path = $pdfPath;
    $invoiceRecord->save();
}

// Return to user
return response()->json([
    'success' => true,
    'invoice_number' => $result->sequence,
    'pdf_url' => $pdfPath ? Storage::url($pdfPath) : null,
]);
```

## Provider-Agnostic Code

The `Invoice` value object ensures your code works with any provider:

```php
function processInvoiceResult($result): array
{
    // This works regardless of which provider was used
    return [
        'sequence' => $result->sequence,
        'id' => $result->id,
        'filename' => $result->output?->fileName(),
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
