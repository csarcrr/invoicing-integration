# Creating an Invoice

Create and send invoices using the Invoicing Integration package with a fluent API.

## Basic Invoice

The simplest invoice requires only an item:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 1000, // Price in cents (10.00)
]);
$invoice->item($item);

$result = $invoice->execute();
```

This creates an **FT (Invoice)** document for a final consumer.

## Adding Client Details

For non-final-consumer invoices, add client information:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;

$client = ClientData::make([
    'name' => 'John Doe',
    'vat' => '123456789',
    'address' => 'Rua das Flores 125',
    'city' => 'Porto',
    'postalCode' => '4410-000',
    'country' => 'PT',
    'email' => 'john.doe@mail.com',
    'phone' => '220123123',
    'irsRetention' => true, // Enable IRS retention
]);

$invoice->client($client);
```

**Validation rules:**

- If you provide a client with only a name (no VAT), an exception is thrown
- If you provide an empty VAT string, an exception is thrown
- For final consumer invoices, do **not** call `client()` at all

## Adding Items

Items support various properties:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;

$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 1000,              // Price in cents
    'quantity' => 2,              // Quantity (default: 1, accepts int or float)
    'note' => 'Product description',
    'percentageDiscount' => 10,   // 10% discount
    'amountDiscount' => 50,       // 0.50 discount in cents
]);

$invoice->item($item);
```

> **Note:** Quantity must be greater than zero. Setting zero or negative values throws an `UnsupportedQuantityException`.

### Multiple Items

```php
$itemA = ItemData::make(['reference' => 'SKU-001']);
$invoice->item($itemA);

$itemB = ItemData::make(['reference' => 'SKU-002']);
$invoice->item($itemB);
```

### Tax Configuration

```php
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;

$item = ItemData::make([
    'reference' => 'SKU-001',
    'tax' => ItemTax::EXEMPT,
    'taxExemptionReason' => TaxExemptionReason::M04,
    'taxExemptionLaw' => TaxExemptionReason::M04->laws()[0],
]);

$invoice->item($item);
```

> **Note:** Tax exemption reason is only valid when `ItemTax::EXEMPT` is set. See
> [Tax Exemptions](invoices/tax-exemption.md?id=working-with-tax-exemptions-1) for the complete list of `M` codes and validation rules.

## Adding Payments

Payments are **required** for certain document types (FR, FS, RG, NC):

```php
use CsarCrr\InvoicingIntegration\Data\PaymentData;use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 2000,
]);
$invoice->payment($payment);
```

### Multiple Payments

```php
$firstPayment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 1000,
]);
$invoice->payment($firstPayment);

$secondPayment = PaymentData::make([
    'method' => PaymentMethod::MONEY,
    'amount' => 1000,
]);
$invoice->payment($secondPayment);
```

> **Important:** Ensure payment method IDs are configured in your provider settings. See [Cegid Vendus Configuration](../providers/cegid-vendus/configuration.md).

## Document Types

Change the document type with `type()`:

```php
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;

$invoice->type(InvoiceType::InvoiceReceipt); // FR
```

**Available types:**

- `InvoiceType::Invoice` — FT (default)
- `InvoiceType::InvoiceReceipt` — FR (requires payment)
- `InvoiceType::InvoiceSimple` — FS (requires payment)
- `InvoiceType::Receipt` — RG (requires payment)
- `InvoiceType::CreditNote` — NC (requires payment, reason, and related document)
- `InvoiceType::Transport` — GT

## Due Date

Set a due date (only valid for FT documents):

```php
use Carbon\Carbon;

$invoice->dueDate(Carbon::now()->addDays(30));
```

> **Note:** Setting a due date on non-FT document types throws an exception.

## Notes

Add notes to the invoice:

```php
$invoice->notes('This is a note for the invoice.');
```

## Transport Details

For invoices with transport information:

```php
use Carbon\Carbon;use CsarCrr\InvoicingIntegration\Data\AddressData;use CsarCrr\InvoicingIntegration\Data\ClientData;

$transport = new AddressData;

$transport->origin()
    ->date(Carbon::now())
    ->time(Carbon::now()->setHour(10)->setMinute(0))
    ->address('Rua das Flores, 125')
    ->city('Porto')
    ->postalCode('4410-200')
    ->country('PT');

$transport->destination()
    ->date(Carbon::now()->addDay())
    ->time(Carbon::now()->setHour(14)->setMinute(0))
    ->address('Rua dos Paninhos, 521')
    ->city('Lisboa')
    ->postalCode('1000-100')
    ->country('PT');

$transport->vehicleLicensePlate('00-AB-00');

// Transport requires client information
$client = ClientData::make([
    'name' => 'Client Name',
    'vat' => 'PT123456789',
]);
$invoice->client($client);
$invoice->transport($transport);
```

**Transport validation rules:**

- Client information is **required** when transport details are provided
- Origin date is **required** when setting transport details
- Country codes must be valid ISO 2-letter codes

## Complete Example

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;use CsarCrr\InvoicingIntegration\Data\ItemData;use CsarCrr\InvoicingIntegration\Data\PaymentData;use CsarCrr\InvoicingIntegration\Enums\InvoiceType;use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create();

// Set client
$client = ClientData::make([
    'name' => 'John Doe',
    'vat' => '123456789',
    'address' => 'Rua das Flores 125',
    'city' => 'Porto',
    'postalCode' => '4410-000',
    'country' => 'PT',
]);
$invoice->client($client);

// Add items
$item = ItemData::make([
    'reference' => 'SKU-001',
    'price' => 1500,
    'quantity' => 2,
    'note' => 'Product A',
]);
$invoice->item($item);

// Add payment (required for FR type)
$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 3000,
]);
$invoice->payment($payment);

// Set document type
$invoice->type(InvoiceType::InvoiceReceipt);

// Add notes
$invoice->notes('Thank you for your purchase.');

// Issue the invoice
$result = $invoice->execute();

// Access the result
echo $result->sequence;  // "FR 01P2025/1"
if ($result->output) {
    $result->output->save('invoices/' . $result->output->fileName());
}
```

---

**Tips:**

- For final consumer invoices, do **not** set any client
- At least one item is required (except for receipts)
- Payment method IDs must be configured in your provider
- See [Output Formats](outputting-invoice.md) for PDF and ESC/POS options

---

Next: [Creating a Receipt (RG)](creating-a-RG-for-an-invoice.md)
