# API Reference

Complete reference for the Invoicing Integration package classes, methods, and enums.

## Client

Entry point for client management operations.

```php
use CsarCrr\InvoicingIntegration\Client;
```

| Method                               | Return Type    | Description                           |
| ------------------------------------ | -------------- | ------------------------------------- |
| `Client::create(ClientData $client)` | `CreateClient` | Creates a new client builder instance |
| `Client::get(ClientData $client)`    | `GetClient`    | Creates a client retrieval instance   |
| `Client::find()`                     | `FindClient`   | Lists/paginates provider clients      |

## CreateClient Contract

The interface returned by `Client::create()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
```

| Method      | Return Type  | Description                                 |
| ----------- | ------------ | ------------------------------------------- |
| `execute()` | `ClientData` | Create the client and return populated data |

## GetClient Contract

The interface returned by `Client::get()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
```

| Method      | Return Type  | Description                                   | Throws                                     |
| ----------- | ------------ | --------------------------------------------- | ------------------------------------------ |
| `execute()` | `ClientData` | Retrieve the client and return populated data | `InvalidArgumentException` (if ID missing) |

## FindClient Contract

Search/paginate provider clients via `Client::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\FindClient;
```

| Method             | Return Type  | Description                                   |
| ------------------ | ------------ | --------------------------------------------- |
| `execute()`        | `self`       | Execute the current page request              |
| `getList()`        | `Collection` | Collection of `ClientDataObject` results      |
| `getPayload()`     | `Collection` | Current request payload (filters, pagination) |
| `next()`           | `self`       | Move to the next page                         |
| `previous()`       | `self`       | Go back one page                              |
| `page(int $page)`  | `self`       | Jump to a specific page                       |
| `getCurrentPage()` | `int`        | Current page index                            |
| `getTotalPages()`  | `int`        | Total pages reported by provider              |

> `next()`, `previous()`, and `page()` throw `NoMorePagesException` when you move
> outside the available range.

---

## Invoice

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

| Method                                         | Parameters                         | Description                                      | Throws |
| ---------------------------------------------- | ---------------------------------- | ------------------------------------------------ | ------ |
| `client(ClientData $client)`                   | ClientData object                  | Set client details (optional for final consumer) | -      |
| `item(Item $item)`                             | Item object                        | Add an item to the invoice                       | -      |
| `payment(Payment $payment)`                    | Payment object                     | Add a payment to the invoice                     | -      |
| `transport(TransportDetails $transport)`       | TransportDetails object            | Set transport details                            | -      |
| `type(InvoiceType $type)`                      | InvoiceType enum                   | Set document type (default: FT)                  | -      |
| `dueDate(Carbon $dueDate)`                     | Carbon date                        | Set due date (FT only)                           | -      |
| `outputFormat(OutputFormat $format)`           | OutputFormat enum                  | Set output format (PDF or ESC/POS)               | -      |
| `relatedDocument(int\|string $doc, ?int $row)` | Document ID/sequence, optional row | Link to related document                         | -      |
| `creditNoteReason(string $reason)`             | Reason text                        | Set credit note reason (NC only)                 | -      |
| `notes(string $notes)`                         | Notes text                         | Add notes to the invoice                         | -      |

### Execution Method

| Method      | Return Type             | Description                         |
| ----------- | ----------------------- | ----------------------------------- |
| `invoice()` | `Invoice` (ValueObject) | Issue the invoice and return result |

### Getter Methods

| Method                  | Return Type         | Description                      |
| ----------------------- | ------------------- | -------------------------------- |
| `getClient()`           | `?ClientData`       | Get the current client           |
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

### ClientData

```php
use CsarCrr\InvoicingIntegration\Facades\ClientData;
```

**Usage:**

```php
ClientData::name('John Doe')->vat('123456789')
```

**Methods (fluent, return self):**

| Method                                  | Parameter         | Description              | Throws |
| --------------------------------------- | ----------------- | ------------------------ | ------ |
| `id(int $id)`                           | Provider ID       | Set provider-assigned ID | -      |
| `name(string $name)`                    | Name string       | Set client name          | -      |
| `vat(string $vat)`                      | VAT/Fiscal ID     | Set tax identification   | -      |
| `address(string $address)`              | Address string    | Set street address       | -      |
| `city(string $city)`                    | City name         | Set city                 | -      |
| `postalCode(string $postalCode)`        | Postal code       | Set postal code          | -      |
| `country(string $country)`              | ISO 2-letter code | Set country              | -      |
| `email(string $email)`                  | Email address     | Set email (validated)    | -      |
| `phone(string $phone)`                  | Phone number      | Set phone                | -      |
| `notes(string $notes)`                  | Notes text        | Set internal notes       | -      |
| `irsRetention(bool $retention)`         | Boolean           | Enable IRS retention     | -      |
| `emailNotification(bool $notification)` | Boolean           | Enable email alerts      | -      |
| `defaultPayDue(int $days)`              | Days              | Set default payment due  | -      |

**Getter Methods:**

| Method                   | Return Type |
| ------------------------ | ----------- |
| `getId()`                | `?int`      |
| `getName()`              | `?string`   |
| `getVat()`               | `?string`   |
| `getAddress()`           | `?string`   |
| `getCity()`              | `?string`   |
| `getPostalCode()`        | `?string`   |
| `getCountry()`           | `?string`   |
| `getEmail()`             | `?string`   |
| `getPhone()`             | `?string`   |
| `getNotes()`             | `?string`   |
| `getIrsRetention()`      | `?bool`     |
| `getEmailNotification()` | `?bool`     |
| `getDefaultPayDue()`     | `?int`      |

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

| Method                                     | Parameter             | Description                   | Throws                         |
| ------------------------------------------ | --------------------- | ----------------------------- | ------------------------------ |
| `reference(string $reference)`             | Product SKU/code      | Set product reference         | -                              |
| `quantity(int\|float $quantity)`           | Quantity              | Set quantity (default: 1)     | `UnsupportedQuantityException` |
| `price(int $price)`                        | Price in cents        | Set unit price                | -                              |
| `note(string $note)`                       | Description           | Set item description          | -                              |
| `tax(ItemTax $tax)`                        | ItemTax enum          | Set tax rate                  | -                              |
| `taxExemption(TaxExemptionReason $reason)` | Exemption enum        | Set exemption reason          | -                              |
| `taxExemptionLaw(string $law)`             | Law reference         | Set exemption law             | -                              |
| `amountDiscount(int $amount)`              | Amount in cents       | Set fixed discount            | -                              |
| `percentageDiscount(int $percent)`         | Percentage            | Set percentage discount       | -                              |
| `relatedDocument(string $doc, int $line)`  | Document, line number | Set related document (for NC) | -                              |

**Getter Methods:**

| Method                    | Return Type           |
| ------------------------- | --------------------- |
| `getReference()`          | `?string`             |
| `getQuantity()`           | `int\|float`          |
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

| Method                          | Parameter          | Description        | Throws |
| ------------------------------- | ------------------ | ------------------ | ------ |
| `method(PaymentMethod $method)` | PaymentMethod enum | Set payment method | -      |
| `amount(int $amount)`           | Amount in cents    | Set payment amount | -      |

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

| Method                           | Parameter         | Description     | Throws |
| -------------------------------- | ----------------- | --------------- | ------ |
| `address(string $address)`       | Address string    | Set address     | -      |
| `city(string $city)`             | City name         | Set city        | -      |
| `postalCode(string $postalCode)` | Postal code       | Set postal code | -      |
| `country(string $country)`       | ISO 2-letter code | Set country     | -      |
| `date(Carbon $date)`             | Carbon date       | Set date        | -      |
| `time(Carbon $time)`             | Carbon time       | Set time        | -      |

**Other Methods:**

| Method                               | Parameter     | Description       | Throws |
| ------------------------------------ | ------------- | ----------------- | ------ |
| `vehicleLicensePlate(string $plate)` | License plate | Set vehicle plate | -      |

---

### Invoice (Response)

```php
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;
```

Returned by `invoice()` method after issuing.

**Methods:**

| Method          | Return Type | Description                             | Throws                          |
| --------------- | ----------- | --------------------------------------- | ------------------------------- |
| `getId()`       | `int`       | Provider's internal ID                  | -                               |
| `getSequence()` | `string`    | Invoice sequence (e.g., "FT 01P2025/1") | -                               |
| `getOutput()`   | `Output`    | Output object for PDF/ESC/POS           | `InvoiceWithoutOutputException` |

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

Portuguese tax exemption codes supported by this package:

| Code | Laws (`laws()[n]`)                                                                                                                                           |
| ---- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| M01  | 0: Art. 16.º, n.º 6, al. a) do CIVA<br>1: Art. 16.º, n.º 6, al. b) do CIVA<br>2: Art. 16.º, n.º 6, al. c) do CIVA<br>3: Art. 16.º, n.º 6, al. d) do CIVA     |
| M02  | 0: Artigo 6.º do Decreto-Lei n.º 198/90, de 19 de junho                                                                                                      |
| M03  | 0: Não utilizar após 2022                                                                                                                                    |
| M04  | 0: Artigo 13.º do CIVA                                                                                                                                       |
| M05  | 0: Artigo 14.º do CIVA                                                                                                                                       |
| M06  | 0: Artigo 15.º do CIVA                                                                                                                                       |
| M07  | 0: Artigo 9.º do CIVA                                                                                                                                        |
| M08  | 0: Utilizar alternativa entre M30 e M43                                                                                                                      |
| M09  | 0: Artigo 60.º do CIVA<br>1: Artigo 72.º n.º 4 do CIVA                                                                                                       |
| M10  | 0: Artigo 53.º n.º 1 do CIVA<br>1: Artigo 57.º do CIVA                                                                                                       |
| M11  | 0: Decreto-Lei n.º 346/85, de 23 de agosto                                                                                                                   |
| M12  | 0: Decreto-Lei n.º 221/85, de 3 de julho                                                                                                                     |
| M13  | 0: Decreto-Lei n.º 199/96, de 18 de outubro                                                                                                                  |
| M14  | 0: Decreto-Lei n.º 199/96, de 18 de outubro                                                                                                                  |
| M15  | 0: Decreto-Lei n.º 199/96, de 18 de outubro                                                                                                                  |
| M16  | 0: Artigo 14.º do RITI                                                                                                                                       |
| M19  | 0: Isenções temporárias em diploma próprio                                                                                                                   |
| M20  | 0: Artigo 59.º-D n.º 2 do CIVA                                                                                                                               |
| M21  | 0: Artigo 72.º n.º 4 do CIVA                                                                                                                                 |
| M25  | 0: Artigo 38.º n.º 1 alínea a) do CIVA                                                                                                                       |
| M26  | 0: Lei n.º 17/2023                                                                                                                                           |
| M30  | 0: Artigo 2.º n.º 1 alínea i) do CIVA                                                                                                                        |
| M31  | 0: Artigo 2.º n.º 1 alínea j) do CIVA                                                                                                                        |
| M32  | 0: Artigo 2.º n.º 1 alínea l) do CIVA                                                                                                                        |
| M33  | 0: Artigo 2.º n.º 1 alínea m) do CIVA                                                                                                                        |
| M34  | 0: Artigo 2.º n.º 1 alínea n) do CIVA                                                                                                                        |
| M40  | 0: Artigo 6.º n.º 6 alínea a) do CIVA, a contrário                                                                                                           |
| M41  | 0: Artigo 8.º n.º 3 do RITI                                                                                                                                  |
| M42  | 0: Decreto-Lei n.º 21/2007, de 29 de janeiro                                                                                                                 |
| M43  | 0: Decreto-Lei n.º 362/99, de 16 de setembro                                                                                                                 |
| M44  | 0: Artigo 6.º do CIVA                                                                                                                                        |
| M45  | 0: Artigo 58.º-A do CIVA                                                                                                                                     |
| M46  | 0: Decreto-Lei n.º 19/2017, de 14 de fevereiro                                                                                                               |
| M99  | 0: Artigo 2.º, n.º 2 do CIVA<br>1: Artigo 3.º, n.º 4 do CIVA<br>2: Artigo 3.º, n.º 6 do CIVA<br>3: Artigo 3.º, n.º 7 do CIVA<br>4: Artigo 4.º, n.º 5 do CIVA |

To print the first legal reference, call `TaxExemptionReason::M04->laws()[0]`. When multiple entries
are available, pick the index that matches your scenario (e.g., `laws()[1]` for `M10` Article 57.º).

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

### Validation Exceptions

| Exception                             | When Thrown                                     |
| ------------------------------------- | ----------------------------------------------- |
| `InvoiceRequiresClientVatException`   | Client provided with empty VAT                  |
| `InvoiceRequiresVatWhenClientHasName` | Client has name but no VAT                      |
| `CreditNoteReasonIsMissingException`  | NC type without credit note reason              |
| `NeedsDateToSetLoadPointException`    | Transport without origin date                   |
| `InvalidCountryException`             | Invalid ISO country code                        |
| `InvoiceWithoutOutputException`       | Calling `getOutput()` when no output is present |
| `UnsupportedQuantityException`        | Item quantity is zero or negative               |
| `MissingRelatedDocumentException`     | Credit note item without related document       |

### Provider Exceptions

| Exception                         | When Thrown                              |
| --------------------------------- | ---------------------------------------- |
| `RequestFailedException`          | Provider returned an error response      |
| `UnauthorizedException`           | Invalid or missing API credentials (401) |
| `FailedReachingProviderException` | Provider is unreachable or returned 500  |

---

For more details, see the source code in `src/` or the tests in `tests/Unit/Invoice/`.
