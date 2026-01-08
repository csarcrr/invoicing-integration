# API Reference

Complete reference for the Invoicing Integration package classes, methods, and enums.

## Invoice Facade

Entry point for creating invoices.

```php
use CsarCrr\InvoicingIntegration\Invoice;
```

| Method              | Return Type     | Description                            |
| ------------------- | --------------- | -------------------------------------- |
| `Invoice::create()` | `CreateInvoice` | Creates a new invoice builder instance |

## CreateInvoice Contract

The builder interface returned by `Invoice::create()`. All methods return `self` for chaining unless otherwise noted.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
```

### Builder Methods

| Method                                         | Parameters                         | Description                                      |
| ---------------------------------------------- | ---------------------------------- | ------------------------------------------------ |
| `client(Client $client)`                       | Client object                      | Set client details (optional for final consumer) |
| `item(Item $item)`                             | Item object                        | Add an item to the invoice                       |
| `payment(Payment $payment)`                    | Payment object                     | Add a payment to the invoice                     |
| `transport(TransportDetails $transport)`       | TransportDetails object            | Set transport details                            |
| `type(InvoiceType $type)`                      | InvoiceType enum                   | Set document type (default: FT)                  |
| `dueDate(Carbon $dueDate)`                     | Carbon date                        | Set due date (FT only)                           |
| `outputFormat(OutputFormat $format)`           | OutputFormat enum                  | Set output format (PDF or ESC/POS)               |
| `relatedDocument(int\|string $doc, ?int $row)` | Document ID/sequence, optional row | Link to related document                         |
| `creditNoteReason(string $reason)`             | Reason text                        | Set credit note reason (NC only)                 |
| `notes(string $notes)`                         | Notes text                         | Add notes to the invoice                         |

### Execution Method

| Method      | Return Type             | Description                         |
| ----------- | ----------------------- | ----------------------------------- |
| `invoice()` | `Invoice` (ValueObject) | Issue the invoice and return result |

### Getter Methods

| Method                  | Return Type         | Description                      |
| ----------------------- | ------------------- | -------------------------------- |
| `getClient()`           | `?Client`           | Get the current client           |
| `getItems()`            | `Collection`        | Get all items                    |
| `getPayments()`         | `Collection`        | Get all payments                 |
| `getTransport()`        | `?TransportDetails` | Get transport details            |
| `getType()`             | `InvoiceType`       | Get document type                |
| `getOutputFormat()`     | `OutputFormat`      | Get output format                |
| `getRelatedDocument()`  | `int\|string\|null` | Get related document reference   |
| `getCreditNoteReason()` | `?string`           | Get credit note reason           |
| `getNotes()`            | `?string`           | Get invoice notes                |
| `getPayload()`          | `Collection`        | Get the prepared request payload |

---

## Value Objects

### Client

```php
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
```

**Constructor:**

```php
new Client()
```

**Methods (fluent, return self):**

| Method                           | Parameter         | Description           |
| -------------------------------- | ----------------- | --------------------- |
| `address(string $address)`       | Address string    | Set street address    |
| `city(string $city)`             | City name         | Set city              |
| `postalCode(string $postalCode)` | Postal code       | Set postal code       |
| `country(string $country)`       | ISO 2-letter code | Set country           |
| `email(string $email)`           | Email address     | Set email (validated) |
| `phone(string $phone)`           | Phone number      | Set phone             |
| `irsRetention(bool $retention)`  | Boolean           | Enable IRS retention  |

**Getter Methods:**

| Method              | Return Type |
| ------------------- | ----------- |
| `getName()`         | `?string`   |
| `getVat()`          | `?string`   |
| `getAddress()`      | `?string`   |
| `getCity()`         | `?string`   |
| `getPostalCode()`   | `?string`   |
| `getCountry()`      | `?string`   |
| `getEmail()`        | `?string`   |
| `getPhone()`        | `?string`   |
| `getIrsRetention()` | `?bool`     |

---

### Item

```php
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
```

**Constructor:**

```php
new Item()
```

**Methods (fluent, return self):**

| Method                                     | Parameter             | Description                   |
| ------------------------------------------ | --------------------- | ----------------------------- |
| `reference(string $reference)`             | Product SKU/code      | Set product reference         |
| `quantity(int $quantity)`                  | Quantity              | Set quantity (default: 1)     |
| `price(int $price)`                        | Price in cents        | Set unit price                |
| `note(string $note)`                       | Description           | Set item description          |
| `tax(ItemTax $tax)`                        | ItemTax enum          | Set tax rate                  |
| `taxExemption(TaxExemptionReason $reason)` | Exemption enum        | Set exemption reason          |
| `taxExemptionLaw(string $law)`             | Law reference         | Set exemption law             |
| `amountDiscount(int $amount)`              | Amount in cents       | Set fixed discount            |
| `percentageDiscount(int $percent)`         | Percentage            | Set percentage discount       |
| `relatedDocument(string $doc, int $line)`  | Document, line number | Set related document (for NC) |

**Getter Methods:**

| Method                    | Return Type           |
| ------------------------- | --------------------- |
| `getReference()`          | `?string`             |
| `getQuantity()`           | `int`                 |
| `getPrice()`              | `?int`                |
| `getNote()`               | `?string`             |
| `getTax()`                | `?ItemTax`            |
| `getTaxExemption()`       | `?TaxExemptionReason` |
| `getTaxExemptionLaw()`    | `?string`             |
| `getAmountDiscount()`     | `?int`                |
| `getPercentageDiscount()` | `?int`                |

---

### Payment

```php
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
```

**Constructor:**

```php
new Payment()
```

**Methods (fluent, return self):**

| Method                          | Parameter          | Description        |
| ------------------------------- | ------------------ | ------------------ |
| `method(PaymentMethod $method)` | PaymentMethod enum | Set payment method |
| `amount(int $amount)`           | Amount in cents    | Set payment amount |

**Getter Methods:**

| Method        | Return Type      |
| ------------- | ---------------- |
| `getMethod()` | `?PaymentMethod` |
| `getAmount()` | `?int`           |

---

### TransportDetails

```php
use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;
```

**Constructor:**

```php
new TransportDetails()
```

**Context Methods:**

| Method          | Return Type | Description                         |
| --------------- | ----------- | ----------------------------------- |
| `origin()`      | `self`      | Set context to origin location      |
| `destination()` | `self`      | Set context to destination location |

**Location Methods (call after origin() or destination()):**

| Method                           | Parameter         | Description     |
| -------------------------------- | ----------------- | --------------- |
| `address(string $address)`       | Address string    | Set address     |
| `city(string $city)`             | City name         | Set city        |
| `postalCode(string $postalCode)` | Postal code       | Set postal code |
| `country(string $country)`       | ISO 2-letter code | Set country     |
| `date(Carbon $date)`             | Carbon date       | Set date        |
| `time(Carbon $time)`             | Carbon time       | Set time        |

**Other Methods:**

| Method                               | Parameter     | Description       |
| ------------------------------------ | ------------- | ----------------- |
| `vehicleLicensePlate(string $plate)` | License plate | Set vehicle plate |

---

### Invoice (Response)

```php
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;
```

Returned by `invoice()` method after issuing.

**Methods:**

| Method          | Return Type | Description                             |
| --------------- | ----------- | --------------------------------------- |
| `getId()`       | `int`       | Provider's internal ID                  |
| `getSequence()` | `string`    | Invoice sequence (e.g., "FT 01P2025/1") |
| `getOutput()`   | `Output`    | Output object for PDF/ESC/POS           |

---

### Output

```php
use CsarCrr\InvoicingIntegration\ValueObjects\Output;
```

**Methods:**

| Method               | Return Type | Description                        |
| -------------------- | ----------- | ---------------------------------- |
| `fileName()`         | `string`    | Auto-generated filename            |
| `save(string $path)` | `string`    | Save to storage, returns full path |

---

## Enums

### InvoiceType

```php
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
```

| Value            | Code | Description        |
| ---------------- | ---- | ------------------ |
| `Invoice`        | FT   | Regular invoice    |
| `InvoiceReceipt` | FR   | Invoice receipt    |
| `InvoiceSimple`  | FS   | Simplified invoice |
| `Receipt`        | RG   | Receipt            |
| `CreditNote`     | NC   | Credit note        |
| `Transport`      | GT   | Transport document |

### PaymentMethod

```php
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
```

| Value             | Description     |
| ----------------- | --------------- |
| `MONEY`           | Cash payment    |
| `MB`              | ATM/Multibanco  |
| `CREDIT_CARD`     | Credit card     |
| `MONEY_TRANSFER`  | Bank transfer   |
| `CURRENT_ACCOUNT` | Current account |

### ItemTax

```php
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
```

| Value          | Description             |
| -------------- | ----------------------- |
| `NORMAL`       | Normal VAT rate (23%)   |
| `INTERMEDIATE` | Intermediate rate (13%) |
| `REDUCED`      | Reduced rate (6%)       |
| `EXEMPT`       | Tax exempt              |
| `OTHER`        | Other tax rate          |

### TaxExemptionReason

```php
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
```

Portuguese tax exemption codes: `M01` through `M30`

Use `TaxExemptionReason::M04->laws()` to get applicable law references.

### OutputFormat

```php
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
```

| Value        | Description                    |
| ------------ | ------------------------------ |
| `PDF_BASE64` | PDF document (base64 encoded)  |
| `ESCPOS`     | ESC/POS thermal printer format |

---

## Exceptions

| Exception                             | When Thrown                        |
| ------------------------------------- | ---------------------------------- |
| `InvoiceRequiresClientVatException`   | Client provided with empty VAT     |
| `InvoiceRequiresVatWhenClientHasName` | Client has name but no VAT         |
| `CreditNoteReasonIsMissingException`  | NC type without credit note reason |
| `NeedsDateToSetLoadPointException`    | Transport without origin date      |
| `InvalidCountryException`             | Invalid ISO country code           |

---

For more details, see the source code in `src/` or the tests in `tests/Unit/Invoice/`.
