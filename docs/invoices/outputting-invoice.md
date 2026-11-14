# Output Formats

When you create an invoice, the provider returns the document as base64-encoded PDF by default, or as ESC/POS for thermal printers. To get the PDF of an invoice that already exists, see [Getting the PDF of an existing invoice](#getting-the-pdf-of-an-existing-invoice).

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoiceData = InvoiceData::make([
    'items' => [
        ItemData::make([
            'reference' => 'LAPTOP-ULTRA-13',
            'note' => 'UltraBook Pro 13"',
            'price' => 129900,
        ]),
    ],
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::CREDIT_CARD,
            'amount' => 129900,
        ]),
    ],
]);

$result = Invoice::create($invoiceData)->execute()->getInvoice();

// Save the PDF
if ($result->output) {
    $path = $result->output->save('invoices/' . $result->output->fileName);
    // $path = "storage/app/invoices/fr_01p2025_1.pdf"
}
```

The file is saved to Laravel's default storage disk (`storage/app/`).

## Choosing Output Format

By default, invoices are returned as PDF. You can request ESC/POS format instead:

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\OutputData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;

$invoiceData = InvoiceData::make([
    // ...items, payments, etc.
    'output' => OutputData::make([
        'format' => OutputFormat::ESCPOS,
    ]),
]);

Invoice::create($invoiceData)->execute();
```

Set the format when building `InvoiceData` so the request payload already
matches the provider requirements.

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

$result->output->save('invoices/' . $result->output->fileName);
```

## Custom File Paths

Save with custom paths or filenames:

```php
$result = $invoice->execute()->getInvoice();
$output = $result->output;

if ($output) {
    // Organize by year/month
    $path = $output->save('invoices/2025/01/' . $output->fileName);

    // Or use a custom name
    $path = $output->save('invoices/order-12345.pdf');

    // Or use the invoice sequence
    $sanitized = str_replace(['/', ' '], '_', $result->sequence);
    $path = $output->save("invoices/{$sanitized}.pdf");
}
```

## Getting the PDF of an existing invoice

Use `Invoice::pdf()` to fetch the PDF of a document already stored in the provider, for example one created earlier with `Invoice::create()`:

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$output = Invoice::pdf(InvoiceData::make(['id' => 123]))->execute()->getOutput();

$output->content; // base64-encoded PDF; null when the provider did not return the file (Moloni)
$output->format;  // OutputFormat::PDF_BASE64
$output->url;     // Moloni PDF link; null on Cegid Vendus

$path = $output->save('invoices/document-123.pdf');
```

- Pass the provider ID (`id`), not the sequence.
- `save()` works as for `create()`. When `content` is `null`, `save()` returns an empty string and writes nothing.
- `url` is the Moloni PDF link, so you can offer the document for download even when `content` is `null`.

### Cegid Vendus

The request is sent to `GET documents/{id}.pdf`. The provider returns the raw PDF, which the package base64-encodes into `content`. `url` is `null`.

### Moloni

The request is sent to `documents/getPDFLink` with `document_id`. Moloni returns a download page in `url`. The package then downloads the PDF from `https://www.moloni.pt/downloads/index.php?action=getDownload&h=<token>&d=<id>`, where `<token>` is the `h` value of the link and `<id>` is the document ID, and stores it base64-encoded in `content`. This download URL is not in the Moloni API documentation.

If that download does not return a PDF, `content` is `null` and `url` is still set, so the developer can open the link instead. Moloni does not return a PDF link for draft documents (status 0).

### Exceptions

- `InvalidArgumentException` with `Invoice ID is required.` when `id` is not set.
- `RequestFailedException` when the provider returns an error, or when Moloni returns an empty PDF link.

## Thermal Printer Integration (ESC/POS)

Print receipts to thermal printers:

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\OutputData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoiceData = InvoiceData::make([
    'items' => [
        ItemData::make([
            'reference' => 'USB-CABLE-C',
            'note' => 'USB-C Cable 2m',
            'price' => 1299,
            'quantity' => 2,
        ]),
    ],
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::MONEY,
            'amount' => 2598,
        ]),
    ],
    'output' => OutputData::make([
        'format' => OutputFormat::ESCPOS,
    ]),
]);

$result = Invoice::create($invoiceData)->execute()->getInvoice();

if ($result->output) {
    // Save the ESC/POS data
    $path = $result->output->save('print-queue/' . $result->output->fileName);

    // Send to your print service
    // PrintService::sendToPrinter($path);
}
```

> [!NOTE]
> ESC/POS support depends on the provider. Check [Features](../features.md) for provider compatibility.

## OutputData Properties and Methods

| Member           | Type           | Description |
| ---------------- | -------------- | ----------- |
| `format`         | `OutputFormat` | Output format (default `PDF_BASE64`) |
| `content`        | `?string`      | The file, base64 encoded |
| `fileName`       | `?string`      | File name used by `save()`; Cegid Vendus `Invoice::create()` sets it to the document number |
| `url`            | `?string`      | Provider download link; set by `Invoice::pdf()` on Moloni only |
| `save(?string $path)` | `string`  | `PDF_BASE64`: decodes `content`, writes it to the `local` disk and returns the path (default `invoices/{fileName}`, sanitized, `.pdf` suffix added). `ESCPOS`: returns the decoded content and writes nothing. Returns `''` when `content` is `null` |

## Complete Workflow Example

Full example with PDF handling:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoiceData = InvoiceData::make([
    'type' => InvoiceType::InvoiceReceipt,
    'client' => ClientData::make([
        'name' => 'Maria Silva',
        'vat' => '123456789',
        'email' => 'maria.silva@email.pt',
    ]),
    'items' => [
        ItemData::make([
            'reference' => 'MONITOR-4K',
            'note' => '27" 4K Monitor',
            'price' => 44999,
        ]),
    ],
    'payments' => [
        PaymentData::make([
            'method' => PaymentMethod::CREDIT_CARD,
            'amount' => 44999,
        ]),
    ],
]);

$result = Invoice::create($invoiceData)->execute()->getInvoice();

// Store in your database
$order = Order::find($orderId);
$order->invoice_sequence = $result->sequence;
$order->invoice_provider_id = $result->id;

// Save and store the PDF path
if ($result->output) {
    $pdfPath = $result->output->save('invoices/' . $result->output->fileName);
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

The default output format is PDF (base64 encoded). Set the format via `OutputData` on your `InvoiceData` before issuing. Use `$result->output?->save($path)` to store the document and `$result->output?->fileName` for the auto-generated filename. Always check `if ($result->output)` before saving, since the provider may not always return a file. Check provider [Features](../features.md) for format support.

> [!NOTE]
> On `Invoice::create()`, Moloni does not return a file yet: `output->content` is `null` and `save()` returns an empty string without writing anything. Use `Invoice::pdf()` to get the PDF of a Moloni invoice.

---

Next: [Using Invoice Data](using-invoice-data.md)
