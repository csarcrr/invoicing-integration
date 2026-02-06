# Creating an Invoice

Create and send invoices using the Invoicing Integration package with a fluent API.

## Basic Invoice

The simplest invoice requires an item and payment. No client details needed for final consumer invoices:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create()
    ->type(InvoiceType::InvoiceReceipt);

// The product they're buying
$item = ItemData::make([
    'reference' => 'PHONE-CASE-BLK',
    'note' => 'Silicone Phone Case - Black',
    'price' => 1499, // 14.99 in cents
]);
$invoice->item($item);

// Cash payment
$payment = PaymentData::make([
    'method' => PaymentMethod::MONEY,
    'amount' => 1499,
]);
$invoice->payment($payment);

$result = $invoice->execute()->getInvoice();
```

This creates an **FR (Invoice-Receipt)** document for a final consumer.

## Invoice with Customer Details

Include customer billing information:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create()
    ->type(InvoiceType::InvoiceReceipt);

// Customer billing details
$client = ClientData::make([
    'name' => 'Ana Rodrigues',
    'vat' => 'PT987654321',
    'email' => 'ana.rodrigues@email.pt',
    'address' => 'Rua do Comércio, 88',
    'city' => 'Porto',
    'postalCode' => '4000-150',
    'country' => 'PT',
    'phone' => '912345678',
]);
$invoice->client($client);

// Products from the order
$laptop = ItemData::make([
    'reference' => 'LAPTOP-ULTRA-13',
    'note' => 'UltraBook Pro 13" - Intel i7, 16GB RAM, 512GB SSD',
    'price' => 129900, // 1299.00 in cents
    'quantity' => 1,
]);
$invoice->item($laptop);

$mouse = ItemData::make([
    'reference' => 'MOUSE-WIRELESS',
    'note' => 'Ergonomic Wireless Mouse',
    'price' => 3999, // 39.99 in cents
    'quantity' => 1,
]);
$invoice->item($mouse);

// Credit card payment
$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 133899, // Total: 1338.99
]);
$invoice->payment($payment);

$result = $invoice->execute()->getInvoice();

// Save the PDF
if ($result->output) {
    $result->output->save('invoices/' . $result->output->fileName());
}
```

**Validation rules:**

- If you provide a client with only a name (no VAT), an exception is thrown
- If you provide an empty VAT string, an exception is thrown
- For final consumer invoices, do **not** call `client()` at all

## Adding Items

Items represent products or services on the invoice:

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;

// Basic product
$item = ItemData::make([
    'reference' => 'HDMI-CABLE-2M',
    'note' => 'HDMI 2.1 Cable - 2 meters',
    'price' => 1999,       // 19.99 in cents
    'quantity' => 3,       // Customer bought 3 units
]);
$invoice->item($item);

// Service with float quantity (e.g., hours)
$service = ItemData::make([
    'reference' => 'REPAIR-SERVICE',
    'note' => 'Computer Repair Service',
    'price' => 5000,       // 50.00 per hour
    'quantity' => 1.5,     // 1.5 hours
]);
$invoice->item($service);
```

> **Note:** Quantity must be greater than zero. Setting zero or negative values throws an `UnsupportedQuantityException`.

### Applying Discounts

You can offer percentage or fixed amount discounts on individual items:

```php
// 10% discount (e.g., loyalty program)
$item = ItemData::make([
    'reference' => 'SPEAKER-BT',
    'note' => 'Bluetooth Speaker',
    'price' => 7999,
    'quantity' => 1,
    'percentageDiscount' => 10, // 10% off
]);

// Fixed discount (e.g., coupon code)
$item = ItemData::make([
    'reference' => 'KEYBOARD-MECH',
    'note' => 'Mechanical Gaming Keyboard',
    'price' => 12999,
    'quantity' => 1,
    'amountDiscount' => 2000, // 20.00 off
]);
```

### Tax Configuration

Set specific tax rates or exemptions:

```php
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;

// Standard VAT (default behavior)
$item = ItemData::make([
    'reference' => 'PRODUCT-001',
    'price' => 1000,
    'tax' => ItemTax::NORMAL, // 23% VAT
]);

// Tax-exempt item (e.g., educational materials)
$item = ItemData::make([
    'reference' => 'TEXTBOOK-MATH',
    'note' => 'Mathematics Textbook - University Level',
    'price' => 4500,
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
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

// Credit card payment
$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 15498,
]);
$invoice->payment($payment);
```

### Split Payments

Customers sometimes pay with multiple methods (e.g., part cash, part card):

```php
// Customer pays 50.00 in cash
$cash = PaymentData::make([
    'method' => PaymentMethod::MONEY,
    'amount' => 5000,
]);
$invoice->payment($cash);

// And the remaining 84.98 with credit card
$card = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 8498,
]);
$invoice->payment($card);
```

> **Important:** Ensure payment method IDs are configured in your provider settings. See [Cegid Vendus Configuration](../providers/cegid-vendus/configuration.md).

## Document Types

Different document types:

```php
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;

$invoice->type(InvoiceType::InvoiceReceipt); // FR - immediate payment
```

**Available types:**

| Type                          | Code | When to Use                          |
| ----------------------------- | ---- | ------------------------------------ |
| `InvoiceType::Invoice`        | FT   | Customer will pay later (default)    |
| `InvoiceType::InvoiceReceipt` | FR   | Customer paid at time of sale        |
| `InvoiceType::InvoiceSimple`  | FS   | Simplified invoice, requires payment |
| `InvoiceType::Receipt`        | RG   | Receipt for an existing FT invoice   |
| `InvoiceType::CreditNote`     | NC   | Refunds and returns                  |
| `InvoiceType::Transport`      | GT   | Transport/shipping documents         |

## Due Date

For FT invoices where payment comes later, set a due date:

```php
use Carbon\Carbon;

$invoice = Invoice::create()
    ->type(InvoiceType::Invoice);

// Payment due in 30 days
$invoice->dueDate(Carbon::now()->addDays(30));
```

> **Note:** Setting a due date on non-FT document types throws an exception.

## Notes

Add notes to the invoice (visible on the document):

```php
$invoice->notes('Thank you for your order! Free returns within 30 days.');
```

## Transport Details

For orders that require shipping with transport documentation:

```php
use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\AddressData;
use CsarCrr\InvoicingIntegration\Data\ClientData;

$transport = new AddressData;

// Where the order ships from (your warehouse)
$transport->origin()
    ->date(Carbon::now())
    ->time(Carbon::now()->setHour(10)->setMinute(0))
    ->address('Zona Industrial de Maia, Lote 42')
    ->city('Maia')
    ->postalCode('4470-000')
    ->country('PT');

// Where it's being delivered
$transport->destination()
    ->date(Carbon::now()->addDay())
    ->time(Carbon::now()->setHour(14)->setMinute(0))
    ->address('Rua do Comércio, 88')
    ->city('Porto')
    ->postalCode('4000-150')
    ->country('PT');

$transport->vehicleLicensePlate('00-AB-00');

// Transport requires customer information
$client = ClientData::make([
    'name' => 'Ana Rodrigues',
    'vat' => 'PT987654321',
]);
$invoice->client($client);
$invoice->transport($transport);
```

**Transport validation rules:**

- Client information is **required** when transport details are provided
- Origin date is **required** when setting transport details
- Country codes must be valid ISO 2-letter codes (PT, ES, FR, etc.)

## Complete Example

Full invoice with multiple items:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

$invoice = Invoice::create()
    ->type(InvoiceType::InvoiceReceipt);

// Customer billing details
$client = ClientData::make([
    'name' => 'Carlos Ferreira',
    'vat' => 'PT567890123',
    'email' => 'carlos@empresa.pt',
    'address' => 'Av. da República, 200',
    'city' => 'Lisboa',
    'postalCode' => '1050-190',
    'country' => 'PT',
]);
$invoice->client($client);

// Order items
$monitor = ItemData::make([
    'reference' => 'MONITOR-4K-27',
    'note' => '27" 4K IPS Monitor - 144Hz',
    'price' => 44999,
    'quantity' => 2,          // Customer bought 2 monitors
]);
$invoice->item($monitor);

$cable = ItemData::make([
    'reference' => 'CABLE-DP-2M',
    'note' => 'DisplayPort 1.4 Cable - 2m',
    'price' => 1499,
    'quantity' => 2,
    'percentageDiscount' => 10, // 10% bundle discount
]);
$invoice->item($cable);

$shipping = ItemData::make([
    'reference' => 'SHIPPING-EXPRESS',
    'note' => 'Express Delivery (next business day)',
    'price' => 1499,
]);
$invoice->item($shipping);

// Payment via bank transfer
$payment = PaymentData::make([
    'method' => PaymentMethod::MONEY_TRANSFER,
    'amount' => 94696, // Total after discounts
]);
$invoice->payment($payment);

// Internal note
$invoice->notes('Order #12345 - Priority shipping requested');

// Issue the invoice
$result = $invoice->execute()->getInvoice();

// Store the result
if ($result->output) {
    $pdfPath = $result->output->save('invoices/' . $result->output->fileName());
}
```

Example invoice result (`$result->toArray()`):

```json
{
    "id": 9876,
    "sequence": "FR 01P2025/1",
    "total": 94696,
    "totalNet": 76988,
    "atcudHash": "FR 01P2025/1 ABC123",
    "output": {
        "format": "pdf_base64",
        "fileName": "fr_01p2025_1.pdf"
    }
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
