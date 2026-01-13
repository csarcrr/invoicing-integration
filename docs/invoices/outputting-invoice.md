# Output Formats

Retrieve and store generated invoice documents in PDF or ESC/POS format.

## Default Output (PDF)

By default, invoices are returned with PDF output:

```php
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

$invoice = Invoice::create();

$item = new Item();
$item->reference('SKU-001');
$invoice->item($item);

$result = $invoice->execute();

// Get the output object
$output = $result->getOutput();

// Get the generated filename
$filename = $output->fileName(); // e.g., "ft_01p2025_1.pdf"

// Save to storage
$path = $output->save('invoices/' . $filename);
echo $path; // Full path to saved file
```

## Selecting Output Format

Choose the output format before issuing the invoice:

```php
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

$invoice = Invoice::create();

$item = new Item();
$item->reference('SKU-001');
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
$output = $result->getOutput();

// Save with custom path
$path = $output->save('invoices/2025/' . $output->fileName());

// Save with custom filename
$path = $output->save('invoices/my-custom-name.pdf');
```

The file is saved to Laravel's local storage disk by default.

## Handling Missing Output

Some providers may not return output data in certain scenarios. Calling `getOutput()` on an invoice without output throws an `InvoiceWithoutOutputException`.

```php
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\InvoiceWithoutOutputException;
```

This exception is thrown when the provider response does not include PDF or ESC/POS data, allowing you to handle this case according to your application's requirements.

## ESC/POS for Thermal Printers

Generate ESC/POS data for direct printing to thermal printers:

```php
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

$invoice = Invoice::create();

$item = new Item();
$item->reference('SKU-001');
$invoice->item($item);
$invoice->outputFormat(OutputFormat::ESCPOS);

$result = $invoice->execute();

// Save ESC/POS data to file
$path = $result->getOutput()->save('print-jobs/' . $result->getOutput()->fileName());

// Or get the raw ESC/POS data
// (implementation depends on how you handle the output)
```

> **Note:** ESC/POS support depends on the provider. Check [Features](../features.md) for provider compatibility.

## Getting Output Format

Check the current output format setting:

```php
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Invoice;

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
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

$invoice = Invoice::create();

// Configure invoice
$item = new Item();
$item->reference('SKU-001');
$item->price(1000);
$invoice->item($item);

$payment = new Payment();
$payment->method(PaymentMethod::CREDIT_CARD);
$payment->amount(1000);
$invoice->payment($payment);

// Set output format
$invoice->outputFormat(OutputFormat::PDF_BASE64);

// Issue invoice
$result = $invoice->execute();

// Access invoice data
$sequence = $result->getSequence();  // "FT 01P2025/1"
$id = $result->getId();              // Provider's internal ID

// Save the document
$output = $result->getOutput();
$path = $output->save("invoices/{$sequence}.pdf");

echo "Invoice {$sequence} saved to: {$path}";
```

---

**Summary:**

- Default output is PDF (base64 encoded)
- Use `outputFormat()` to select PDF or ESC/POS before issuing
- Use `getOutput()->save($path)` to store the document
- Use `getOutput()->fileName()` for the auto-generated filename
- Check provider [Features](../features.md) for format support

---

Next: [Using Invoice Data](using-invoice-data.md)
