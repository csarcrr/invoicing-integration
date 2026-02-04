# Output Formats

Retrieve and store generated invoice documents in PDF or ESC/POS format.

## Default Output (PDF)

By default, invoices are returned with PDF output:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

$item = ItemData::make(['reference' => 'SKU-001']);
$invoice->item($item);

$result = $invoice->execute();

// Get the output object (may be null)
$output = $result->output;

if (! $output) {
    // Handle scenarios where the provider does not return the file
    return;
}

// Get the generated filename
$filename = $output->fileName(); // e.g., "ft_01p2025_1.pdf"

// Save to storage
$path = $output->save('invoices/' . $filename);
```

Example saved file info:

```json
{
    "fileName": "ft_01p2025_1.pdf",
    "relativePath": "invoices/ft_01p2025_1.pdf",
    "absolutePath": "storage/app/invoices/ft_01p2025_1.pdf"
}
```

## Selecting Output Format

Choose the output format before issuing the invoice:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Enums\OutputFormat;use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

$item = ItemData::make(['reference' => 'SKU-001']);
$invoice->item($item);

// Request PDF output (default)
$invoice->outputFormat(OutputFormat::PDF_BASE64);

// Or request ESC/POS output for thermal printers
$invoice->outputFormat(OutputFormat::ESCPOS);

$result = $invoice->execute();
```

## Available Formats

| Format  | Enum Value                 | Description                   |
| ------- | -------------------------- | ----------------------------- |
| PDF     | `OutputFormat::PDF_BASE64` | PDF document (base64 encoded) |
| ESC/POS | `OutputFormat::ESCPOS`     | Thermal printer format        |

## Saving Output

The `save()` method stores the output file and returns the full path:

```php
$result = $invoice->execute();
$output = $result->output;

if ($output) {
    // Save with custom path
    $path = $output->save('invoices/2025/' . $output->fileName());

    // Save with custom filename
    $path = $output->save('invoices/my-custom-name.pdf');
}
```

The file is saved to Laravel's local storage disk by default.

## Handling Missing Output

Some providers may not return output data in certain scenarios. When that happens the `$result->output` property is `null`.

Always guard your file operations:

```php
$output = $result->output;

if (! $output) {
    // Notify your team or fall back to the provider portal.
    return;
}

$output->save('invoices/fallback.pdf');
```

## ESC/POS for Thermal Printers

Generate ESC/POS data for direct printing to thermal printers:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Enums\OutputFormat;use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

$item = ItemData::make(['reference' => 'SKU-001']);
$invoice->item($item);
$invoice->outputFormat(OutputFormat::ESCPOS);

$result = $invoice->execute();

if ($result->output) {
    // Save ESC/POS data to file
    $path = $result->output->save('print-jobs/' . $result->output->fileName());

    // Or get the raw ESC/POS data
    // (implementation depends on how you handle the output)
}
```

> **Note:** ESC/POS support depends on the provider. Check [Features](../features.md) for provider compatibility.

## Getting Output Format

Check the current output format setting:

```php
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();
$format = $invoice->getOutputFormat(); // Returns OutputFormat enum

if ($format === OutputFormat::PDF_BASE64) {
    // Handle PDF
} else if ($format === OutputFormat::ESCPOS) {
    // Handle thermal print
}
```

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Data\PaymentData;use CsarCrr\InvoicingIntegration\Enums\OutputFormat;use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

// Configure invoice
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

// Set output format
$invoice->outputFormat(OutputFormat::PDF_BASE64);

// Issue invoice
$result = $invoice->execute();

// Access invoice data
$sequence = $result->sequence;  // "FT 01P2025/1"
$id = $result->id;              // Provider's internal ID

// Save the document if provided by the provider
if ($result->output) {
    $path = $result->output->save("invoices/{$sequence}.pdf");
}
```

Example result plus output metadata:

```json
{
    "invoice": {
        "id": 321,
        "sequence": "FT 01P2025/1"
    },
    "output": {
        "fileName": "ft_01p2025_1.pdf",
        "relativePath": "invoices/ft_01p2025_1.pdf",
        "storagePath": "storage/app/invoices/ft_01p2025_1.pdf"
    }
}
```

---

**Summary:**

- Default output is PDF (base64 encoded)
- Use `outputFormat()` to select PDF or ESC/POS before issuing
- Use `$result->output?->save($path)` to store the document
- Use `$result->output?->fileName()` for the auto-generated filename
- Check provider [Features](../features.md) for format support

---

Next: [Using Invoice Data](using-invoice-data.md)
