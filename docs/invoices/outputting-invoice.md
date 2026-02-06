# Output Formats

Invoices can be returned as PDF or ESC/POS data.

## Saving the Invoice PDF

The provider returns the document as base64-encoded PDF:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

$item = ItemData::make([
    'reference' => 'LAPTOP-ULTRA-13',
    'note' => 'UltraBook Pro 13"',
    'price' => 129900,
]);
$invoice->item($item);

$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 129900,
]);
$invoice->payment($payment);

$result = $invoice->execute()->getInvoice();

// Save the PDF
if ($result->output) {
    $path = $result->output->save('invoices/' . $result->output->fileName());
    // $path = "storage/app/invoices/fr_01p2025_1.pdf"
}
```

The file is saved to Laravel's default storage disk (`storage/app/`).

## Choosing Output Format

By default, invoices are returned as PDF. You can request ESC/POS format instead:

```php
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;

// Request PDF (default)
$invoice->outputFormat(OutputFormat::PDF_BASE64);

// Or request ESC/POS for thermal printers
$invoice->outputFormat(OutputFormat::ESCPOS);
```

Set the format before calling `execute()`.

## Available Formats

| Format  | Enum Value                 | Use Case                        |
| ------- | -------------------------- | ------------------------------- |
| PDF     | `OutputFormat::PDF_BASE64` | Email, archive, customer portal |
| ESC/POS | `OutputFormat::ESCPOS`     | Thermal receipt printers at POS |

## Handling Missing Output

Sometimes the provider might not return output data (rare, but possible). Always check before saving:

```php
$result = $invoice->execute()->getInvoice();

if (! $result->output) {
    // Log for investigation
    Log::warning('Invoice issued without output', [
        'sequence' => $result->sequence,
        'id' => $result->id,
    ]);

    // Fall back to provider portal or retry later
    return;
}

$result->output->save('invoices/' . $result->output->fileName());
```

## Custom File Paths

Save with custom paths or filenames:

```php
$result = $invoice->execute()->getInvoice();
$output = $result->output;

if ($output) {
    // Organize by year/month
    $path = $output->save('invoices/2025/01/' . $output->fileName());

    // Or use a custom name
    $path = $output->save('invoices/order-12345.pdf');

    // Or use the invoice sequence
    $sanitized = str_replace(['/', ' '], '_', $result->sequence);
    $path = $output->save("invoices/{$sanitized}.pdf");
}
```

## Thermal Printer Integration (ESC/POS)

Print receipts to thermal printers:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Simple invoice
$invoice = Invoice::create();

$item = ItemData::make([
    'reference' => 'USB-CABLE-C',
    'note' => 'USB-C Cable 2m',
    'price' => 1299,
    'quantity' => 2,
]);
$invoice->item($item);

$payment = PaymentData::make([
    'method' => PaymentMethod::MONEY,
    'amount' => 2598,
]);
$invoice->payment($payment);

// Request ESC/POS format for the receipt printer
$invoice->outputFormat(OutputFormat::ESCPOS);

$result = $invoice->execute()->getInvoice();

if ($result->output) {
    // Save the ESC/POS data
    $path = $result->output->save('print-queue/' . $result->output->fileName());

    // Send to your print service
    // PrintService::sendToPrinter($path);
}
```

> **Note:** ESC/POS support depends on the provider. Check [Features](../features.md) for provider compatibility.

## Output Object Methods

The output object provides these methods:

| Method        | Returns        | Description                                        |
| ------------- | -------------- | -------------------------------------------------- |
| `fileName()`  | `?string`      | Auto-generated filename (e.g., `fr_01p2025_1.pdf`) |
| `save($path)` | `string`       | Save to storage, returns full path                 |
| `content()`   | `string`       | Raw output content (base64 for PDF)                |
| `format()`    | `OutputFormat` | The output format enum                             |
| `getPath()`   | `?string`      | Path after saving (null if not yet saved)          |

## Complete Workflow Example

Full example with PDF handling:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Process the checkout
$invoice = Invoice::create()
    ->type(InvoiceType::InvoiceReceipt);

$client = ClientData::make([
    'name' => 'Maria Silva',
    'vat' => 'PT123456789',
    'email' => 'maria.silva@email.pt',
]);
$invoice->client($client);

$item = ItemData::make([
    'reference' => 'MONITOR-4K',
    'note' => '27" 4K Monitor',
    'price' => 44999,
]);
$invoice->item($item);

$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 44999,
]);
$invoice->payment($payment);

$result = $invoice->execute()->getInvoice();

// Store in your database
$order = Order::find($orderId);
$order->invoice_sequence = $result->sequence;
$order->invoice_provider_id = $result->id;

// Save and store the PDF path
if ($result->output) {
    $pdfPath = $result->output->save('invoices/' . $result->output->fileName());
    $order->invoice_pdf_path = $pdfPath;

    // Queue email to customer
    SendInvoiceEmail::dispatch($order);
}

$order->save();

// Return success to frontend
return response()->json([
    'success' => true,
    'invoice_number' => $result->sequence,
    'pdf_url' => $order->invoice_pdf_path
        ? Storage::url($order->invoice_pdf_path)
        : null,
]);
```

---

**Summary:**

- Default output is PDF (base64 encoded)
- Use `outputFormat()` to select PDF or ESC/POS before issuing
- Use `$result->output?->save($path)` to store the document
- Use `$result->output?->fileName()` for the auto-generated filename
- Always check `if ($result->output)` before saving
- Check provider [Features](../features.md) for format support

---

Next: [Using Invoice Data](using-invoice-data.md)
