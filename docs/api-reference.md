# API Reference

Complete reference for the Invoicing Integration package classes, methods, and enums.

## Client

Entry point for client management operations.

```php
use CsarCrr\InvoicingIntegration\Facades\Client;
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
| `getList()`        | `Collection` | `Collection<ClientData>` results              |
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
use CsarCrr\InvoicingIntegration\Facades\Invoice;
```

| Method              | Return Type     | Description                            |
| ------------------- | --------------- | -------------------------------------- |
| `Invoice::create()` | `CreateInvoice` | Creates a new invoice builder instance |

> **Facade vs. Action:** The `Invoice` facade resolves the underlying
> `InvoiceAction` class from the service container. Prefer the facade for day-to-day
> usage. Resolve `InvoiceAction` directly only when you need to inject the class
> (e.g., in constructors) or swap the implementation during testing. The facade is
> now the canonical entry point; there is no standalone `Invoice` class you need to
> instantiate manually.

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
| `payment(PaymentData $payment)`                | PaymentData object                 | Add a payment to the invoice                     | -      |
| `transport(TransportData $transport)`          | TransportData object               | Set transport details                            | -      |
| `type(InvoiceType $type)`                      | InvoiceType enum                   | Set document type (default: FT)                  | -      |
| `dueDate(Carbon $dueDate)`                     | Carbon date                        | Set due date (FT only)                           | -      |
| `outputFormat(OutputFormat $format)`           | OutputFormat enum                  | Set output format (PDF or ESC/POS)               | -      |
| `relatedDocument(int\|string $doc, ?int $row)` | Document ID/sequence, optional row | Link to related document                         | -      |
| `creditNoteReason(string $reason)`             | Reason text                        | Set credit note reason (NC only)                 | -      |
| `notes(string $notes)`                         | Notes text                         | Add notes to the invoice                         | -      |

### Execution Method

| Method      | Return Type             | Description                         |
| ----------- | ----------------------- | ----------------------------------- |
| `execute()` | `Invoice` (ValueObject) | Issue the invoice and return result |

### Getter Methods

| Method                  | Return Type         | Description                      |
| ----------------------- | ------------------- | -------------------------------- |
| `getClient()`           | `?ClientData`       | Get the current client           |
| `getItems()`            | `Collection`        | Get all items                    |
| `getPayments()`         | `Collection`        | Get all payments                 |
| `getTransport()`        | `?TransportData`    | Get transport details            |
| `getType()`             | `InvoiceType`       | Get document type                |
| `getOutputFormat()`     | `OutputFormat`      | Get output format                |
| `getRelatedDocument()`  | `int\|string\|null` | Get related document reference   |
| `getCreditNoteReason()` | `?string`           | Get credit note reason           |
| `getNotes()`            | `?string`           | Get invoice notes                |
| `getPayload()`          | `Collection`        | Get the prepared request payload |

---

## Value Objects & DTOs

All DTOs extend `spatie/laravel-data\Data`, expose a `::make()` factory, and rely
on **public typed properties** rather than getters. Access values with
`$dto->property` and rely on `Optional`-typed attributes to differentiate between
`null` and "not provided" states. Always call `::make([...])` (or resolve from the
container) so validation rules and transformers run before the HTTP request.

### ClientData

**Usage:**

```php
ClientData::make([
    'name' => 'John Doe',
    'vat' => '123456789',
    'email' => 'john@example.com',
]);
```

**Public Properties:**

| Property            | Type               | Description                         |
| ------------------- | ------------------ | ----------------------------------- |
| `id`                | `Optional<int>`    | Provider-assigned identifier        |
| `name`              | `Optional<string>` | Client name (auto-trimmed)          |
| `vat`               | `Optional<string>` | VAT / fiscal ID                     |
| `email`             | `Optional<string>` | Email (validated)                   |
| `country`           | `Optional<string>` | ISO 3166-1 alpha-2 code             |
| `city`              | `Optional<string>` | City name                           |
| `address`           | `Optional<string>` | Street address                      |
| `postalCode`        | `Optional<string>` | Postal/ZIP code                     |
| `phone`             | `Optional<string>` | Phone number                        |
| `notes`             | `Optional<string>` | Internal notes                      |
| `defaultPayDue`     | `Optional<int>`    | Default payment due (days)          |
| `externalReference` | `Optional<string>` | Provider-specific reference         |
| `status`            | `Optional<string>` | Provider status                     |
| `emailNotification` | `Optional<bool>`   | Whether to send documents via email |
| `irsRetention`      | `Optional<bool>`   | IRS withholding flag                |
| `date`              | `Optional<Carbon>` | Provider creation date (`Y-m-d`)    |

> Access DTO values directly: `$client->name`, `$client->vat`, etc. Use
> `$client->toArray()` when you need a payload-friendly structure (includes
> provider-specific metadata stored on the DTO).

---

### Item

**Instantiation:**

```php
$item = ItemData::make([
    'reference' => 'SKU-001',
    'quantity' => 2,
    'price' => 1500,
    'note' => 'Consulting hours',
    'tax' => ItemTax::EXEMPT,
    'taxExemptionReason' => TaxExemptionReason::M04,
    'taxExemptionLaw' => TaxExemptionReason::M04->laws()[0],
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FT 01P2025/1',
        'row' => 1,
    ]),
]);
```

**Properties:**

| Property             | Type                            | Description                                        | Validation / Notes                                                        |
| -------------------- | ------------------------------- | -------------------------------------------------- | ------------------------------------------------------------------------- |
| `reference`          | `null\|int\|string`             | Product SKU or code                                | -                                                                         |
| `quantity`           | `null\|int\|float`              | Quantity (defaults to `1`)                         | Must be `> 0`, otherwise `UnsupportedQuantityException`                   |
| `price`              | `?int`                          | Unit price in cents                                | -                                                                         |
| `note`               | `?string`                       | Optional line description                          | -                                                                         |
| `type`               | `?ItemType`                     | Item classification (default: `ItemType::Product`) | -                                                                         |
| `tax`                | `?ItemTax`                      | VAT rate                                           | Required for `taxExemptionReason`                                         |
| `taxExemptionReason` | `?TaxExemptionReason`           | VAT exemption justification                        | Requires `tax === ItemTax::EXEMPT`, otherwise `ExemptionCanOnlyBeUsed...` |
| `taxExemptionLaw`    | `?string`                       | Legal reference for the exemption                  | Requires `taxExemptionReason`, otherwise `ExemptionLawCanOnlyBeUsed...`   |
| `amountDiscount`     | `?int`                          | Fixed discount in cents                            | -                                                                         |
| `percentageDiscount` | `?int`                          | Percentage discount                                | -                                                                         |
| `relatedDocument`    | `?RelatedDocumentReferenceData` | Reference to original document row (NC items)      | Required when issuing credit notes                                        |

---

### PaymentData

```php

```

**Instantiation:**

```php
$payment = PaymentData::make([
    'method' => PaymentMethod::CREDIT_CARD,
    'amount' => 5000,
]);
```

**Properties:**

| Property | Type             | Description               |
| -------- | ---------------- | ------------------------- |
| `method` | `?PaymentMethod` | Payment method enum value |
| `amount` | `?int`           | Payment amount (in cents) |

`PaymentData` extends `Spatie\LaravelData\Data`, so you can provide plain
arrays or DTOs to `::make()` and let transformers/validation prepare the payload
before the HTTP request is issued.

---

### TransportData

```php
use Carbon\Carbon;use CsarCrr\InvoicingIntegration\Data\AddressData;use CsarCrr\InvoicingIntegration\Data\TransportData;

$origin = AddressData::make([
    'date' => Carbon::parse('2025-01-10'),
    'time' => Carbon::parse('2025-01-10 10:00'),
    'address' => 'Rua das Flores, 125',
    'city' => 'Porto',
    'postalCode' => '4410-200',
    'country' => 'PT',
]);

$destination = AddressData::make([
    'date' => Carbon::parse('2025-01-11'),
    'time' => Carbon::parse('2025-01-11 14:00'),
    'address' => 'Rua dos Paninhos, 521',
    'city' => 'Lisboa',
    'postalCode' => '1000-100',
    'country' => 'PT',
]);

$transport = TransportData::make([
    'origin' => $origin->toArray(),
    'destination' => $destination->toArray(),
    'vehicleLicensePlate' => '00-AB-00',
]);
```

**TransportData Properties:**

| Property              | Type          | Description                                          |
| --------------------- | ------------- | ---------------------------------------------------- |
| `origin`              | `AddressData` | Load/location details (date, time, address, country) |
| `destination`         | `AddressData` | Delivery/unload details                              |
| `vehicleLicensePlate` | `?string`     | Truck/license plate identifier                       |

**AddressData Fields:**

| Field        | Type      | Description                         |
| ------------ | --------- | ----------------------------------- |
| `address`    | `?string` | Street and house number             |
| `city`       | `?string` | City                                |
| `postalCode` | `?string` | Postal code                         |
| `country`    | `string`  | ISO 3166-1 alpha-2 code (validated) |
| `date`       | `?Carbon` | Optional load/unload date           |
| `time`       | `?Carbon` | Optional load/unload time           |

---

### Invoice (Response)

```php

```

Returned by `execute()` method after issuing.

**Properties:**

| Property    | Type      | Description                             | Notes                                   |
| ----------- | --------- | --------------------------------------- | --------------------------------------- |
| `id`        | `int`     | Provider's internal ID                  | -                                       |
| `sequence`  | `string`  | Invoice sequence (e.g., "FT 01P2025/1") | -                                       |
| `total`     | `int`     | Total amount in cents (gross)           | -                                       |
| `totalNet`  | `int`     | Net total amount in cents               | -                                       |
| `atcudHash` | `?string` | ATCUD hash (Portugal AT code)           | -                                       |
| `output`    | `?Output` | Output object for PDF/ESC/POS           | `null` when the provider omits the file |

---

### Output

```php
use CsarCrr\InvoicingIntegration\ValueObjects\Output;
```

**Methods:**

| Method | Return Type | Description |
| Method | Return Type | Description |
| -------------------------- | -------------- | ---------------------------------- |
| `fileName()` | `?string` | Auto-generated filename |
| `save(?string $path)` | `string` | Save to storage, returns full path |
| `get()` | `string` | Alias for `save()`, returns path |
| `content()` | `string` | Raw output content |
| `format()` | `OutputFormat` | Output format enum |
| `getPath()` | `?string` | Path after saving (null if unsaved)|

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

### ItemType

```php
use CsarCrr\InvoicingIntegration\Enums\ItemType;
```

| Value        | Description      |
| ------------ | ---------------- |
| `Product`    | Physical product |
| `Service`    | Service          |
| `Other`      | Other type       |
| `Tax`        | Tax (VAT)        |
| `SpecialTax` | Special tax      |

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

| Exception                                         | When Thrown                                 |
| ------------------------------------------------- | ------------------------------------------- |
| `InvoiceRequiresClientVatException`               | Client provided with empty VAT              |
| `InvoiceRequiresVatWhenClientHasName`             | Client has name but no VAT                  |
| `CreditNoteReasonIsMissingException`              | NC type without credit note reason          |
| `NeedsDateToSetLoadPointException`                | Transport without origin date               |
| `InvalidCountryException`                         | Invalid ISO country code                    |
| `UnsupportedQuantityException`                    | Item quantity is zero or negative           |
| `MissingRelatedDocumentException`                 | Credit note item without related document   |
| `ExemptionCanOnlyBeUsedWithExemptTaxException`    | Tax exemption set without `ItemTax::EXEMPT` |
| `ExemptionLawCanOnlyBeUsedWithExemptionException` | Exemption law set without exemption reason  |

### Provider Exceptions

| Exception                         | When Thrown                              |
| --------------------------------- | ---------------------------------------- |
| `RequestFailedException`          | Provider returned an error response      |
| `UnauthorizedException`           | Invalid or missing API credentials (401) |
| `FailedReachingProviderException` | Provider is unreachable or returned 500  |

---

For more details, see the source code in `src/` or the tests in `tests/Unit/Invoice/`.
