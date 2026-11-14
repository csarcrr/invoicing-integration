# API Reference

Complete reference for the Invoicing Integration package classes, methods, and enums. This page documents all public APIs you'll use when integrating invoicing into your Laravel application.

## Client

Entry point for managing clients.

```php
use CsarCrr\InvoicingIntegration\Facades\Client;
```

| Method                                      | Return Type    | Description                           |
| ------------------------------------------- | -------------- | ------------------------------------- |
| `Client::create(ClientData $client)`        | `ShouldCreateClient` | Creates a new client builder instance |
| `Client::update(ClientData $client)`        | `ShouldUpdateClient` | Creates a client update instance      |
| `Client::delete(ClientData $client)`        | `ShouldDeleteClient` | Creates a client deletion instance    |
| `Client::get(ClientData $client)`           | `ShouldGetClient`    | Creates a client retrieval instance   |
| `Client::find(?ClientData $filters = null)` | `ShouldFindClient`   | Lists/paginates provider clients      |

## ShouldCreateClient Contract

The interface returned by `Client::create()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldCreateClient;
```

| Method      | Return Type  | Description                                 |
| ----------- | ------------ | ------------------------------------------- |
| `execute()` | `ClientData` | Create the client and return populated data |

## ShouldUpdateClient Contract

The interface returned by `Client::update()`. Supported on Cegid Vendus and Moloni. See [Updating a Client](clients/updating-a-client.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldUpdateClient;
```

| Method      | Return Type  | Description                                                    | Throws                                     |
| ----------- | ------------ | -------------------------------------------------------------- | ------------------------------------------ |
| `execute()` | `self`       | Send the update request and store unhandled response fields as additional data | `InvalidArgumentException` (if ID missing) |
| `getClient()` | `ClientData` | The updated client. On Moloni, the `ClientData` you passed in | -                                    |

## ShouldDeleteClient Contract

The interface returned by `Client::delete()`. Supported on Cegid Vendus and Moloni. See [Deleting a Client](clients/deleting-a-client.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldDeleteClient;
```

| Method      | Return Type | Description                                                   | Throws                                                                   |
| ----------- | ----------- | ------------------------------------------------------------- | ------------------------------------------------------------------------ |
| `execute()` | `void`      | Send the deletion request; returns nothing                    | `InvalidArgumentException` (if ID missing, thrown by `Client::delete()`), `RequestFailedException` (if the provider rejects the deletion) |
| `getUrl()`  | `string`    | The request URL the deletion is sent to                       | -                                                                        |
| `getPayload()` | `Collection` | Inspect the payload sent to the provider (empty on Cegid Vendus, `customer_id` on Moloni) | - |

## ShouldGetClient Contract

The interface returned by `Client::get()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldGetClient;
```

| Method      | Return Type  | Description                                   | Throws                                     |
| ----------- | ------------ | --------------------------------------------- | ------------------------------------------ |
| `execute()` | `ClientData` | Retrieve the client and return populated data | `InvalidArgumentException` (if ID missing, thrown by `Client::get()`) |
| `getUrl()`  | `string`     | The request URL the retrieval is sent to      | -                                          |
| `getPayload()` | `Collection` | Inspect the payload sent to the provider (empty on Cegid Vendus, `customer_id` on Moloni) | - |

## ShouldFindClient Contract

Search/paginate provider clients via `Client::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldFindClient;
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
| `getTotalPages()`  | `?int`       | Total pages, `null` when not reported         |
| `getClient()`      | `ClientData` | Current filter DTO (email, VAT, status, etc.) |

> `next()`, `previous()`, and `page()` throw `NoMorePagesException` when you move
> outside the available range.

---

## Item

Entry point for managing items (product catalog). Use this facade to create products and services independently of invoices.

```php
use CsarCrr\InvoicingIntegration\Facades\Item;
```

| Method                              | Return Type        | Description                          |
| ----------------------------------- | ------------------ | ------------------------------------ |
| `Item::create(ItemData $item)`      | `ShouldCreateItem` | Creates a new item builder instance  |
| `Item::update(ItemData $item)`      | `ShouldUpdateItem` | Creates a partial item update instance |
| `Item::delete(ItemData $item)`      | `ShouldDeleteItem` | Creates an item deletion instance    |
| `Item::get(ItemData $item)`         | `ShouldGetItem`    | Creates an item retrieval instance   |
| `Item::find(?ItemData $item = null)` | `ShouldFindItem`  | Lists/paginates provider catalog items |

## ShouldGetItem Contract

The interface returned by `Item::get()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
```

| Method      | Return Type | Description                                                  | Throws                                     |
| ----------- | ----------- | ------------------------------------------------------------ | ------------------------------------------ |
| `execute()` | `self`      | Send the GET request and populate the returned `ItemData`    | `InvalidArgumentException` (if ID missing, thrown by `Item::get()`) |
| `getItem()` | `ItemData`  | Access the hydrated DTO (`name`, `description`, `reference`, `barcode`, `category`, `controlStock`, `enabled`, `type`, `tax`, `price`, and additional data) | — |
| `getUrl()`  | `string`    | The request URL the retrieval is sent to                    | —                                          |
| `getPayload()` | `Collection` | Inspect the payload sent to the provider (empty on Cegid Vendus, `product_id` and `with_invisible` on Moloni) | — |

Provider response fields that the package does not explicitly map onto `ItemData` are accessible via `getAdditionalData()` on the returned DTO:

```php
$item = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

$item->name;                // mapped from provider's `title` field
$item->description;         // mapped directly
$item->reference;           // mapped directly
$item->barcode;             // mapped from provider's `barcode` (Cegid Vendus) or `ean` (Moloni)
$item->price;               // gross price in cents
$item->getAdditionalData(); // provider fields the package does not handle
```

## ShouldFindItem Contract

Search/paginate provider catalog items via `Item::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFindItem;
```

| Method             | Return Type  | Description                                   |
| ------------------ | ------------ | --------------------------------------------- |
| `execute()`        | `self`       | Execute the current page request              |
| `getList()`        | `Collection` | `Collection<ItemData>` results                |
| `getPayload()`     | `Collection` | Current request payload (filters, pagination) |
| `next()`           | `self`       | Move to the next page                         |
| `previous()`       | `self`       | Go back one page                              |
| `page(int $page)`  | `self`       | Jump to a specific page                       |
| `getCurrentPage()` | `int`        | Current page index                            |
| `getTotalPages()`  | `?int`       | Total pages, `null` when not reported         |

> `next()`, `previous()`, and `page()` throw `NoMorePagesException` when you move
> outside the available range.

---

## ShouldCreateItem Contract

The interface returned by `Item::create()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
```

| Method         | Return Type  | Description                                                                                 |
| -------------- | ------------ | ------------------------------------------------------------------------------------------- |
| `execute()`    | `self`       | Send the create request and populate the response                                           |
| `getItem()`    | `ItemData`   | Access the hydrated DTO (includes provider-assigned `id`)                                   |
| `getPayload()` | `Collection` | Inspect the payload sent to the provider (debugging)                                        |

Fields from the provider response that the package does not explicitly handle are stored on the returned `ItemData` and accessible via `getAdditionalData()`:

```php
$created = Item::create($item)->execute()->getItem();

$created->getAdditionalData(); // fields from the provider response not handled by the package
```

---

## ShouldUpdateItem Contract

The interface returned by `Item::update()`. Supported on Cegid Vendus and Moloni. See [Updating an Item](items/updating-an-item.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldUpdateItem;
```

| Method         | Return Type  | Description                                                                                          |
| -------------- | ------------ | ---------------------------------------------------------------------------------------------------- |
| `execute()`    | `self`       | Send the update request and store unhandled response fields as additional data                       |
| `getItem()`    | `ItemData`   | The updated item. On Moloni, the current product with the updated fields merged over it              |
| `getPayload()` | `Collection` | Inspect the payload. On Moloni this requests the current product first                               |

Throws `InvalidArgumentException` when `id` is missing.

## ShouldDeleteItem Contract

The interface returned by `Item::delete()`. Supported on Cegid Vendus and Moloni. See [Deleting an Item](items/deleting-an-item.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldDeleteItem;
```

| Method      | Return Type | Description                                                   | Throws                                                                   |
| ----------- | ----------- | ------------------------------------------------------------- | ------------------------------------------------------------------------ |
| `execute()` | `void`      | Send the deletion request; returns nothing                    | `RequestFailedException` (if the provider rejects the deletion)          |
| `getUrl()`  | `string`    | The request URL the deletion is sent to                       | —                                                                        |
| `getPayload()` | `Collection` | Inspect the payload sent to the provider (empty on Cegid Vendus, `product_id` on Moloni) | — |

---

## DueDate

Entry point for managing due dates (payment terms). Supported on Moloni; Cegid Vendus throws `OperationNotSupportedException`.

```php
use CsarCrr\InvoicingIntegration\Facades\DueDate;
```

| Method                                 | Return Type           | Description                             |
| -------------------------------------- | --------------------- | --------------------------------------- |
| `DueDate::create(DueDateData $dueDate)` | `ShouldCreateDueDate` | Creates a new due date builder instance |
| `DueDate::find()`                      | `ShouldFindDueDate`   | Lists/paginates provider due dates      |

## ShouldCreateDueDate Contract

The interface returned by `DueDate::create()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldCreateDueDate;
```

| Method         | Return Type   | Description                                               |
| -------------- | ------------- | --------------------------------------------------------- |
| `execute()`    | `self`        | Send the create request and populate the response         |
| `getDueDate()` | `DueDateData` | Access the hydrated DTO (includes provider-assigned `id`) |
| `getPayload()` | `Collection`  | Inspect the payload sent to the provider (debugging)      |

## ShouldFindDueDate Contract

Paginate provider due dates via `DueDate::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldFindDueDate;
```

| Method             | Return Type  | Description                           |
| ------------------ | ------------ | ------------------------------------- |
| `execute()`        | `self`       | Execute the current page request      |
| `getList()`        | `Collection` | `Collection<DueDateData>` results     |
| `getPayload()`     | `Collection` | Current request payload (pagination)  |
| `next()`           | `self`       | Move to the next page                 |
| `previous()`       | `self`       | Go back one page                      |
| `page(int $page)`  | `self`       | Jump to a specific page               |
| `getCurrentPage()` | `int`        | Current page index                    |
| `getTotalPages()`  | `?int`       | Total pages, `null` when not reported |

> `previous()` and `page()` throw `NoMorePagesException` when you move below page 1.

---

## ItemStock

Entry point for moving item stock. Supported on Cegid Vendus and Moloni.

```php
use CsarCrr\InvoicingIntegration\Facades\ItemStock;
```

| Method                                               | Return Type                | Description                          |
| ---------------------------------------------------- | -------------------------- | ------------------------------------ |
| `ItemStock::move(StockMovementData $movement)`   | `ShouldMoveItemStock`   | Adds or removes stock of an item   |

## ShouldMoveItemStock Contract

The interface returned by `ItemStock::move()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldMoveItemStock;
```

| Method              | Return Type            | Description                                                   |
| ------------------- | ---------------------- | ------------------------------------------------------------- |
| `execute()`         | `self`                 | Send the movement                                             |
| `getMovement()`     | `StockMovementData` | Access the movement (includes provider-assigned `id`)         |
| `getPayload()`      | `Collection`           | Inspect the payload sent to the provider (debugging)          |

---

## Company (Facade)

Entry point for listing the companies the provider user has access to. Supported on Moloni; Cegid Vendus throws `OperationNotSupportedException`.

```php
use CsarCrr\InvoicingIntegration\Facades\Company;
```

| Method            | Return Type         | Description                     |
| ----------------- | ------------------- | ------------------------------- |
| `Company::find()` | `ShouldFindCompany` | Lists the provider's companies  |

## ShouldFindCompany Contract

The interface returned by `Company::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Company\ShouldFindCompany;
```

| Method      | Return Type  | Description                       |
| ----------- | ------------ | --------------------------------- |
| `execute()` | `self`       | Send the request                  |
| `getList()` | `Collection` | `Collection<CompanyData>` results |

---

## Tax (Facade)

Entry point for managing the taxes stored in the provider. Supported on Moloni; Cegid Vendus throws `OperationNotSupportedException`. Not to be confused with the [`ItemTax` enum](#itemtax).

```php
use CsarCrr\InvoicingIntegration\Facades\Tax;
```

| Method                       | Return Type       | Description                        |
| ---------------------------- | ----------------- | ---------------------------------- |
| `Tax::create(TaxData $tax)`  | `ShouldCreateTax` | Creates a new tax builder instance |
| `Tax::find()`                | `ShouldFindTax`   | Lists the provider's taxes         |

## ShouldCreateTax Contract

The interface returned by `Tax::create()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldCreateTax;
```

| Method         | Return Type  | Description                                               |
| -------------- | ------------ | --------------------------------------------------------- |
| `execute()`    | `self`       | Send the create request and populate the response         |
| `getTax()`     | `TaxData`    | Access the hydrated DTO (includes provider-assigned `id`) |
| `getPayload()` | `Collection` | Inspect the payload sent to the provider (debugging)      |

## ShouldFindTax Contract

The interface returned by `Tax::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldFindTax;
```

| Method      | Return Type  | Description                   |
| ----------- | ------------ | ----------------------------- |
| `execute()` | `self`       | Send the request              |
| `getList()` | `Collection` | `Collection<TaxData>` results |

---

## Unit (Facade)

Entry point for listing the units stored in the provider. Supported on Cegid Vendus and Moloni. Not to be confused with the [`Unit` enum](#unit).

```php
use CsarCrr\InvoicingIntegration\Facades\Unit;
```

| Method         | Return Type      | Description                |
| -------------- | ---------------- | -------------------------- |
| `Unit::find()` | `ShouldFindUnit` | Lists the provider's units |

## ShouldFindUnit Contract

The interface returned by `Unit::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit\ShouldFindUnit;
```

| Method      | Return Type  | Description                    |
| ----------- | ------------ | ------------------------------ |
| `execute()` | `self`       | Send the request               |
| `getList()` | `Collection` | `Collection<UnitData>` results |

---

## Warehouse (Facade)

Entry point for managing the warehouses (Moloni) or stores (Cegid Vendus) stored in the provider. `find()` is supported on Cegid Vendus and Moloni; `create()` is supported on Moloni, and Cegid Vendus throws `OperationNotSupportedException`.

```php
use CsarCrr\InvoicingIntegration\Facades\Warehouse;
```

| Method                                    | Return Type           | Description                               |
| ----------------------------------------- | --------------------- | ----------------------------------------- |
| `Warehouse::create(WarehouseData $warehouse)` | `ShouldCreateWarehouse` | Creates a new warehouse builder instance |
| `Warehouse::find()`                       | `ShouldFindWarehouse` | Lists the provider's warehouses or stores |

## ShouldCreateWarehouse Contract

The interface returned by `Warehouse::create()`. See [Creating a Warehouse](warehouses/creating-a-warehouse.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldCreateWarehouse;
```

| Method             | Return Type     | Description                                                                                       |
| ------------------ | --------------- | ------------------------------------------------------------------------------------------------- |
| `execute()`        | `self`          | Send the create request and populate the returned `WarehouseData`; throws `RequestFailedException` if the provider rejects the warehouse |
| `getWarehouse()`   | `WarehouseData` | The created warehouse, with the provider-assigned `id`; on Moloni the generated `code` is in `getAdditionalData()` |
| `getUrl()`         | `string`        | The request URL the warehouse is sent to                                                          |
| `getPayload()`     | `Collection`    | Inspect the payload sent to the provider (`title` and the optional fields on Moloni)              |

## ShouldFindWarehouse Contract

The interface returned by `Warehouse::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldFindWarehouse;
```

| Method      | Return Type  | Description                          |
| ----------- | ------------ | ------------------------------------ |
| `execute()` | `self`       | Send the request                     |
| `getList()` | `Collection` | `Collection<WarehouseData>` results  |
| `getUrl()`  | `string`     | Request URL the operation builds     |

---

## TaxExemption (Facade)

Entry point for listing the tax exemption reasons stored in the provider. Supported on Cegid Vendus and Moloni.

```php
use CsarCrr\InvoicingIntegration\Facades\TaxExemption;
```

| Method                  | Return Type               | Description                                 |
| ----------------------- | ------------------------- | ------------------------------------------- |
| `TaxExemption::find()`  | `ShouldFindTaxExemption`  | Lists the provider's tax exemption reasons  |

## ShouldFindTaxExemption Contract

The interface returned by `TaxExemption::find()`. See [Finding Tax Exemptions](taxes/finding-tax-exemptions.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\TaxExemption\ShouldFindTaxExemption;
```

| Method         | Return Type  | Description                                                 |
| -------------- | ------------ | ----------------------------------------------------------- |
| `execute()`    | `self`       | Send the request                                            |
| `getList()`    | `Collection` | `Collection<TaxExemptionData>` results                      |
| `getUrl()`     | `string`     | Request URL the operation builds                            |
| `getPayload()` | `Collection` | Inspect the payload sent to the provider (debugging)        |

---

## DocumentType (Facade)

Entry point for listing the document types stored in the provider. Supported on Cegid Vendus and Moloni.

```php
use CsarCrr\InvoicingIntegration\Facades\DocumentType;
```

| Method                                               | Return Type              | Description                         |
| ---------------------------------------------------- | ------------------------ | ----------------------------------- |
| `DocumentType::find(?LanguageData $language = null)` | `ShouldFindDocumentType` | Lists the provider's document types |

## ShouldFindDocumentType Contract

The interface returned by `DocumentType::find()`. See [Finding Document Types](document-types/finding-document-types.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DocumentType\ShouldFindDocumentType;
```

| Method         | Return Type  | Description                                                                                  |
| -------------- | ------------ | -------------------------------------------------------------------------------------------- |
| `execute()`    | `self`       | Send the request                                                                             |
| `getList()`    | `Collection` | `Collection<DocumentTypeData>` results                                                       |
| `getUrl()`     | `string`     | Request URL the operation builds                                                             |
| `getPayload()` | `Collection` | Inspect the payload sent to the provider (empty on Cegid Vendus; `language_id` on Moloni, added during `execute()`) |

---

## Category (Facade)

Entry point for the categories stored in the provider. Supported on Cegid Vendus and Moloni.

```php
use CsarCrr\InvoicingIntegration\Facades\Category;
```

| Method                                         | Return Type          | Description                                          |
| ---------------------------------------------- | -------------------- | ---------------------------------------------------- |
| `Category::create(CategoryData $category)`     | `ShouldCreateCategory` | Creates a new category builder instance            |
| `Category::find(?CategoryData $category = null)` | `ShouldFindCategory` | Lists the provider's categories, by `parentId` level |
| `Category::get(CategoryData $category)`        | `ShouldGetCategory`  | Fetches one category by `id`                         |

## ShouldCreateCategory Contract

The interface returned by `Category::create()`. See [Creating a Category](categories/creating-a-category.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldCreateCategory;
```

| Method          | Return Type    | Description                                                                 |
| --------------- | -------------- | --------------------------------------------------------------------------- |
| `execute()`     | `self`         | Send the create request and populate the returned `CategoryData`; throws `RequestFailedException` if the provider rejects the category |
| `getCategory()` | `CategoryData` | The created category, with the provider-assigned `id`                       |
| `getUrl()`      | `string`       | The request URL the category is sent to                                     |
| `getPayload()`  | `Collection`   | Inspect the payload sent to the provider (`title` on Cegid Vendus, `name` and `parent_id` on Moloni) |

## ShouldFindCategory Contract

The interface returned by `Category::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFindCategory;
```

| Method             | Return Type  | Description                                  |
| ------------------ | ------------ | -------------------------------------------- |
| `execute()`        | `self`       | Execute the current page request             |
| `getList()`        | `Collection` | `Collection<CategoryData>` results           |
| `getPayload()`     | `Collection` | Current request payload (parent, pagination) |
| `next()`           | `self`       | Move to the next page                        |
| `previous()`       | `self`       | Go back one page                             |
| `page(int $page)`  | `self`       | Jump to a specific page                      |
| `getCurrentPage()` | `int`        | Current page index                           |
| `getTotalPages()`  | `?int`       | Total pages; always `1` on Moloni            |

> `next()`, `previous()`, and `page()` throw `NoMorePagesException` when you move
> outside the available range.

## ShouldGetCategory Contract

The interface returned by `Category::get()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldGetCategory;
```

| Method          | Return Type    | Description                                                    |
| --------------- | -------------- | -------------------------------------------------------------- |
| `execute()`     | `self`         | Send the request; throws `InvalidArgumentException` without an `id` |
| `getCategory()` | `CategoryData` | The fetched category                                           |

---

## PaymentMethod (Facade)

Entry point for managing the payment methods stored in the provider. `find()` is supported on Cegid Vendus and Moloni; `create()` is supported on Moloni, and Cegid Vendus throws `OperationNotSupportedException`. Not to be confused with the [`PaymentMethod` enum](#paymentmethod).

```php
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;
```

| Method                                                    | Return Type                 | Description                                   |
| --------------------------------------------------------- | --------------------------- | --------------------------------------------- |
| `PaymentMethod::create(PaymentMethodData $paymentMethod)` | `ShouldCreatePaymentMethod` | Creates a new payment method builder instance |
| `PaymentMethod::find()`                                   | `ShouldFindPaymentMethod`   | Lists/paginates provider payment methods      |

## ShouldCreatePaymentMethod Contract

The interface returned by `PaymentMethod::create()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldCreatePaymentMethod;
```

| Method               | Return Type         | Description                                               |
| -------------------- | ------------------- | --------------------------------------------------------- |
| `execute()`          | `self`              | Send the create request and populate the response         |
| `getPaymentMethod()` | `PaymentMethodData` | Access the hydrated DTO (includes provider-assigned `id`) |
| `getPayload()`       | `Collection`        | Inspect the payload sent to the provider (debugging)      |

## ShouldFindPaymentMethod Contract

Paginate provider payment methods via `PaymentMethod::find()`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldFindPaymentMethod;
```

| Method             | Return Type  | Description                             |
| ------------------ | ------------ | --------------------------------------- |
| `execute()`        | `self`       | Execute the current page request        |
| `getList()`        | `Collection` | `Collection<PaymentMethodData>` results |
| `getPayload()`     | `Collection` | Current request payload (pagination)    |
| `next()`           | `self`       | Move to the next page                   |
| `previous()`       | `self`       | Go back one page                        |
| `page(int $page)`  | `self`       | Jump to a specific page                 |
| `getCurrentPage()` | `int`        | Current page index                      |
| `getTotalPages()`  | `?int`       | Total pages, `null` when not reported   |

> `previous()` and `page()` throw `NoMorePagesException` when you move below page 1.

---

## Invoice

Entry point for issuing invoices. Use this facade to create FT, FR, FS, RG, NC, and GT documents.

```php
use CsarCrr\InvoicingIntegration\Facades\Invoice;
```

| Method                                  | Return Type     | Description                                          |
| --------------------------------------- | --------------- | ---------------------------------------------------- |
| `Invoice::create(InvoiceData $invoice)` | `ShouldCreateInvoice` | Creates a builder seeded with the provided DTO state |
| `Invoice::get(InvoiceData $invoice)`   | `ShouldGetInvoice`    | Creates a retrieval instance for a stored document   |
| `Invoice::pdf(InvoiceData $invoice)`   | `ShouldGetInvoicePdf` | Creates a retrieval instance for the PDF of a stored document |
| `Invoice::cancel(InvoiceData $invoice)` | `ShouldCancelInvoice` | Creates a cancellation instance for a stored document |
| `Invoice::find(?InvoiceData $invoice = null)` | `ShouldFindInvoice` | Lists/paginates stored documents of one type, optionally for one client |

> [!TIP]
> The `Invoice` facade resolves the underlying `InvoiceAction` class from the service container. Prefer the facade for day-to-day usage. Resolve `InvoiceAction` directly only when you need to inject the class (e.g., in constructors) or swap the implementation during testing.

## ShouldCreateInvoice Contract

The contract returned by `Invoice::create()` focuses on executing the request
and inspecting the resulting DTO. All configuration lives on `InvoiceData`.

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
```

| Method         | Return Type   | Description                                                       |
| -------------- | ------------- | ----------------------------------------------------------------- |
| `execute()`    | `self`        | Issue the HTTP request using the data contained in `InvoiceData`  |
| `asDraft()`    | `static`      | Create the document as a draft (Moloni); ignored by Cegid Vendus  |
| `getInvoice()` | `InvoiceData` | Access the hydrated DTO (includes provider response data/output)  |
| `getPayload()` | `Collection`  | Inspect the payload that will be sent to the provider (debugging) |

> Need to change values after instantiating your DTO? Mutate the
> `InvoiceData` instance (its properties are public) before passing it to
> `Invoice::create()`, or create a new DTO via `InvoiceData::from([...])`.

## ShouldGetInvoice Contract

The contract returned by `Invoice::get()`. See [Getting an Invoice](invoices/getting-an-invoice.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoice;
```

| Method          | Return Type   | Description                                                   | Throws                                                                   |
| --------------- | ------------- | ------------------------------------------------------------- | ------------------------------------------------------------------------ |
| `execute()`     | `self`        | Send the GET request and populate the returned `InvoiceData`  | `InvalidArgumentException` (if ID missing, thrown by `Invoice::get()`), `RequestFailedException` (if no document has the ID) |
| `getInvoice()`  | `InvoiceData` | The fetched invoice; unmapped response fields are additional data | -                                                                    |
| `getUrl()`      | `string`      | The request URL the retrieval is sent to                      | -                                                                    |
| `getPayload()`  | `Collection`  | Inspect the payload sent to the provider (empty on Cegid Vendus, `document_id` on Moloni) | -                                                                    |

## ShouldGetInvoicePdf Contract

The contract returned by `Invoice::pdf()`. See [Getting the PDF of an Invoice](invoices/outputting-invoice.md#getting-the-pdf-of-an-existing-invoice).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoicePdf;
```

| Method          | Return Type   | Description                                                   | Throws                                                                   |
| --------------- | ------------- | ------------------------------------------------------------- | ------------------------------------------------------------------------ |
| `execute()`     | `self`        | Send the request and populate the returned `OutputData`       | `InvalidArgumentException` (if ID missing, thrown by `Invoice::pdf()`), `RequestFailedException` (Moloni, if no PDF link is returned) |
| `getOutput()`   | `OutputData`  | The PDF as base64 `content`; `url` is the Moloni PDF link     | -                                                                        |
| `getUrl()`      | `string`      | The URL of the first request (Moloni: the PDF link request; Cegid Vendus: the PDF document) | -                                       |
| `getPayload()`  | `Collection`  | Inspect the payload of the first request (empty on Cegid Vendus, `document_id` on Moloni) | -                                       |

## ShouldCancelInvoice Contract

The contract returned by `Invoice::cancel()`. See [Cancelling an Invoice](invoices/cancelling-an-invoice.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCancelInvoice;
```

| Method      | Return Type | Description                                                   | Throws                                                                   |
| ----------- | ----------- | ------------------------------------------------------------- | ------------------------------------------------------------------------ |
| `execute()` | `void`      | Send the cancellation request; returns nothing                | `InvalidArgumentException` (if ID missing, thrown by `Invoice::cancel()`), `RequestFailedException` (if the provider rejects the cancellation) |
| `getUrl()`  | `string`    | The request URL the cancellation is sent to                  | -                                                                        |
| `getPayload()` | `Collection` | Inspect the payload sent to the provider (`status` on Cegid Vendus, `document_id` and optional `observation` on Moloni) | -                                                                        |

## ShouldFindInvoice Contract

The contract returned by `Invoice::find()`. See [Finding Invoices](invoices/finding-invoices.md).

```php
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldFindInvoice;
```

| Method             | Return Type  | Description                                        | Throws                                                                   |
| ------------------ | ------------ | -------------------------------------------------- | ------------------------------------------------------------------------ |
| `execute()`        | `self`       | Execute the current page request                   | `RequestFailedException` (if the provider rejects the search)            |
| `getList()`        | `Collection` | `Collection<InvoiceData>` results                  | -                                                                        |
| `getPayload()`     | `Collection` | Current request payload (type, client, pagination) | -                                                                        |
| `getUrl()`         | `string`     | The request URL the search is sent to (Moloni: one endpoint per document type; Cegid Vendus: `/documents`) | - |
| `next()`           | `self`       | Move to the next page                              | `NoMorePagesException` (Cegid Vendus, past the last page)                |
| `previous()`       | `self`       | Go back one page                                   | `NoMorePagesException` (before the first page)                           |
| `page(int $page)`  | `self`       | Jump to a specific page                            | `NoMorePagesException` (below 1, or past the total on Cegid Vendus)      |
| `getCurrentPage()` | `int`        | Current page index                                 | -                                                                        |
| `getTotalPages()`  | `?int`       | Total pages, `null` when not reported (Moloni)     | -                                                                        |

---

## Value Objects & DTOs

All DTOs extend `spatie/laravel-data\Data`, expose a `::make()` factory, and rely
on **public typed properties** rather than getters. Access values with
`$dto->property` and rely on `Optional`-typed attributes to differentiate between
`null` and "not provided" states. Always call `::make([...])` (or resolve from the
container) so validation rules and transformers run before the HTTP request.

`Optional` and `null` serve different purposes:

```php
// null means "send null to the API"
new ItemData(discount: null);

// Optional means "omit this field entirely from the payload"
new ItemData(discount: Optional::create());
```

### InvoiceData

`InvoiceData` serves a dual role: it is the **input DTO** you populate and pass to `Invoice::create()`, and it is also the **response object** returned by `execute()->getInvoice()`. After a successful call the same class is returned, now hydrated with provider response fields (`id`, `sequence`, `total`, `totalNet`, `atcudHash`, and `output`). There is no separate response type to import.

Entry point for invoice creation.

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;

$invoiceData = InvoiceData::make([
    'type' => InvoiceType::InvoiceReceipt,
    'client' => $clientData ?? null,
    'items' => [$item1, $item2],
    'payments' => [$payment],
    'creditNoteReason' => null,
]);
```

| Property           | Type                      | Description                                     |
| ------------------ | ------------------------- | ----------------------------------------------- |
| `type`             | `InvoiceType`             | Document type (defaults to `Invoice`)           |
| `items`            | `Collection<ItemData>`    | Invoice lines; required except for receipts     |
| `payments`         | `Collection<PaymentData>` | Payment records (required for FR, FS, RG, NC)   |
| `client`           | `?ClientData`             | Customer information                            |
| `transport`        | `?TransportData`          | Transport/movement of goods info                |
| `creditNoteReason` | `?string`                 | Required for credit notes                       |
| `relatedDocument`  | `?string`                 | Related document of a non-credit-note document. On RG, the invoice it settles: its `sequence` on Cegid Vendus, its `id` on Moloni |
| `notes`            | `?string`                 | Additional notes (printed on document)          |
| `dueDate`          | `?DueDateTerm`            | Payment term (FT only); throws `Exception` if set on non-FT types |
| `output`           | `OutputData`              | Preferred output format (PDF default)           |

> `InvoiceData` implements `DataNeedsValidation`, so misconfigured payloads throw
> before any HTTP request runs.

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
| `id`                | `Optional<string>` | Provider-assigned identifier        |
| `name`              | `Optional<string>` | Client name (auto-trimmed)          |
| `vat`               | `Optional<string\|int>` | VAT / fiscal ID                |
| `email`             | `Optional<string>` | Email (validated)                   |
| `country`           | `Optional<string>` | ISO 3166-1 alpha-2 code             |
| `city`              | `Optional<string>` | City name                           |
| `address`           | `Optional<string>` | Street address                      |
| `postalCode`        | `Optional<string>` | Postal/ZIP code                     |
| `phone`             | `Optional<string>` | Phone number                        |
| `notes`             | `Optional<string>` | Internal notes                      |
| `defaultPayDue`     | `Optional<DueDateTerm>` | Default payment due (days)     |
| `discount`          | `Optional<float>`  | Discount percentage (0 to 100)      |
| `creditLimit`       | `Optional<float>`  | Credit limit (0 or more)            |
| `deliveryMethodId`  | `Optional<int>`    | Delivery method id (0 or more)      |
| `paymentMethodId`   | `Optional<int>`    | Payment method id (0 or more)       |
| `paymentDay`        | `Optional<int>`    | Payment day (0 or more)             |
| `salesmanId`        | `Optional<int>`    | Salesman id (0 or more)             |
| `reference`         | `Optional<string>` | Your own code for the client (Cegid Vendus `external_reference`, Moloni `number`) |
| `status`            | `Optional<string>` | Provider status                     |
| `emailNotification` | `Optional<bool>`   | Whether to send documents via email |
| `irsRetention`      | `Optional<bool>`   | IRS withholding flag                |
| `date`              | `Optional<Carbon>` | Provider creation date (`Y-m-d`)    |

> Access DTO values directly: `$client->name`, `$client->vat`, etc. Use
> `$client->toArray()` when you need a payload-friendly structure. Provider
> response fields the package does not map are left out of `toArray()` and
> `toJson()`; read them with `getAdditionalData()`. This applies to every DTO
> the package hydrates from a provider response, except the clients in a
> `Client::find()` list.

---

### ItemData

`ItemData` is used both for invoice line items and for product catalog management (via `Item::create()`).

**Instantiation:**

```php
$item = ItemData::make([
    'name' => 'Consulting Service',
    'reference' => 'SKU-001',
    'quantity' => 2,
    'price' => 1500,
    'note' => 'Consulting hours',
    'tax' => ItemTax::EXEMPT,
    'taxExemptionReason' => 'M04',
    'taxExemptionLaw' => 'Artigo 13.º do CIVA',
    'relatedDocument' => RelatedDocumentReferenceData::make([
        'documentId' => 'FT 01P2025/1',
        'row' => 1,
    ]),
]);
```

**Properties:**

| Property             | Type                            | Default              | Description                                        | Validation / Notes                                                        |
| -------------------- | ------------------------------- | -------------------- | -------------------------------------------------- | ------------------------------------------------------------------------- |
| `id`                 | `?int`                          | `null`               | Provider-assigned identifier (set after create)    | -                                                                         |
| `name`               | `?string`                       | `null`               | Item/product name                                  | Required for catalog item creation                                        |
| `description`        | `?string`                       | `null`               | Longer description of the item                     | -                                                                         |
| `reference`          | `null\|int\|string`             | `null`               | Product SKU or code                                | -                                                                         |
| `quantity`           | `null\|int\|float`              | `null`               | Quantity; invoice lines send `1` when `null`                        | Must be `> 0`, otherwise `UnsupportedQuantityException`                   |
| `price`              | `?int`                          | `null`               | Unit price in cents                                | -                                                                         |
| `note`               | `?string`                       | `null`               | Optional line description (invoice use)            | -                                                                         |
| `barcode`            | `?string`                       | `null`               | EAN/barcode                                        | -                                                                         |
| `type`               | `?ItemType`                     | `null`               | Item classification; create and Vendus invoice lines send `Product` when `null`                                | -                                                                         |
| `tax`                | `?ItemTax`                      | `null`               | VAT rate                                           | Required for `taxExemptionReason`                                         |
| `taxExemptionReason` | `?string`                       | `null`               | VAT exemption reason code (see `TaxExemption::find()`) | Requires `tax === ItemTax::EXEMPT`, otherwise `ExemptionCanOnlyBeUsed...` |
| `taxExemptionLaw`    | `?string`                       | `null`               | Legal reference for the exemption                  | Requires `taxExemptionReason`, otherwise `ExemptionLawCanOnlyBeUsed...`   |
| `amountDiscount`     | `?int`                          | `null`               | Fixed discount in cents                            | Not sent on Moloni                                                        |
| `percentageDiscount` | `?int`                          | `null`               | Percentage discount                                | -                                                                         |
| `category`           | `?CategoryData`                 | `null`               | Product category                                   | -                                                                         |
| `unit`               | `?ShouldBeUnit`                 | `null`               | Unit of measure (use `Unit` enum or custom). Read back from the provider's unit id as a `Unit` case | Must be mapped in config; throws `CouldNotGetUnitIdException` if missing  |
| `controlStock`       | `?bool`                         | `null`               | Whether to track stock; create sends `true` when `null`               | -                                                                         |
| `enabled`            | `?bool`                         | `null`               | Whether the item is active; create sends `true` when `null`. Read from Vendus `status` and Moloni `visibility_id` | -                                                                         |
| `relatedDocument`    | `?RelatedDocumentReferenceData` | `null`               | Reference to original document row (NC items)      | Required when issuing credit notes                                        |
| `stock`              | `?Collection<int, ItemStockData>` | `null`             | Stock per warehouse, filled by `Item::get()`       | -                                                                         |

---

### CategoryData

Represents a product category for catalog items, and the categories returned by `Category::create()`, `Category::find()` and `Category::get()`.

```php
use CsarCrr\InvoicingIntegration\Data\CategoryData;

$category = CategoryData::make([
    'id' => 5,
]);
```

| Property   | Type      | Description                                                                 |
| ---------- | --------- | --------------------------------------------------------------------------- |
| `id`       | `?int`    | Provider-assigned category ID                                               |
| `name`     | `?string` | Category name                                                               |
| `parentId` | `?int`    | Parent category ID (Moloni only); `null` for a top-level category and on Vendus |

---

### DueDateData

Represents a due date (payment term) stored in the provider.

```php
use CsarCrr\InvoicingIntegration\Data\DueDateData;

$dueDate = DueDateData::make([
    'name' => '30 dias',
    'days' => 30,
]);
```

| Property | Type     | Description                            |
| -------- | -------- | -------------------------------------- |
| `name`   | `string` | Required. Name of the payment term     |
| `days`   | `int`    | Required. Number of days until payment |
| `id`     | `?int`   | Provider-assigned due date ID          |

---

### StockMovementData

Represents a stock movement. Used to move stock.

```php
use CsarCrr\InvoicingIntegration\Data\StockMovementData;

$movement = StockMovementData::make([
    'itemId' => 1542,
    'quantity' => -5,
]);
```

| Property      | Type              | Description                                                         |
| ------------- | ----------------- | ------------------------------------------------------------------- |
| `id`          | `?int`            | Provider-assigned movement ID; stays `null` on Cegid Vendus |
| `itemId`   | `?int`            | Item ID; required to move                                        |
| `quantity`    | `null\|int\|float` | Units to add (positive) or remove (negative); required to move; never `0` |
| `date`        | `?Carbon`         | Movement date and time; the current time on Moloni when `null`; sent in `obs` on Cegid Vendus |
| `warehouseId` | `?int`            | Warehouse of the movement                                           |
| `notes`       | `?string`         | Notes for the movement                                              |

`make()` throws `UnsupportedQuantityException` when `quantity` is `0`.

---

### ItemStockData

The stock of an item in one warehouse, returned in `ItemData->stock` by `Item::get()`.

| Property      | Type         | Description                                                   |
| ------------- | ------------ | ------------------------------------------------------------- |
| `itemId`      | `int`        | ID of the item                                                |
| `warehouseId` | `int`        | Warehouse (Moloni `warehouse_id`, Cegid Vendus `store_id`)    |
| `quantity`    | `int\|float` | Stock of the item in that warehouse                           |

---

### CompanyData

Represents a company the provider user has access to.

```php
use CsarCrr\InvoicingIntegration\Data\CompanyData;
```

| Property | Type      | Description                    |
| -------- | --------- | ------------------------------ |
| `name`   | `string`  | Required. Name of the company  |
| `id`     | `?int`    | Provider-assigned company ID   |
| `vat`    | `?string` | VAT number of the company      |

---

### TaxData

Represents a tax stored in the provider.

```php
use CsarCrr\InvoicingIntegration\Data\TaxData;
```

| Property          | Type                  | Description                                          |
| ----------------- | --------------------- | ---------------------------------------------------- |
| `name`            | `string`              | Required. Name of the tax                            |
| `id`              | `?int`                | Provider-assigned tax ID                             |
| `rate`            | `?float`              | Rate in percent                                      |
| `type`            | `?ItemTax`            | Kind of VAT, `null` when the tax is not VAT          |
| `fiscalZone`      | `?string`             | Fiscal zone, e.g. `PT`, `PT-AC`, `PT-MA`             |
| `exemptionReason` | `?string`             | Exemption reason code, set on 0% taxes               |

---

### UnitData

Represents a unit stored in the provider.

```php
use CsarCrr\InvoicingIntegration\Data\UnitData;
```

| Property    | Type      | Description                                |
| ----------- | --------- | ------------------------------------------ |
| `name`      | `string`  | Required. Name of the unit                 |
| `id`        | `?int`    | Provider-assigned unit ID                  |
| `shortName` | `?string` | Short name (Moloni only; `null` on Vendus) |

---

### WarehouseData

Represents a warehouse (Moloni) or store (Cegid Vendus) stored in the provider.

```php
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
```

| Property     | Type      | Description                                                        |
| ------------ | --------- | ------------------------------------------------------------------ |
| `id`         | `?int`    | Provider-assigned warehouse or store ID                            |
| `name`       | `?string` | Name of the warehouse or store                                     |
| `address`    | `?string` | Street address                                                     |
| `city`       | `?string` | City                                                               |
| `postalCode` | `?string` | Postal code, sent as `zip_code` on Moloni                          |
| `country`    | `?string` | ISO 3166-1 alpha-2 code, looked up as `country_id` on Moloni       |

`address`, `city`, `postalCode` and `country` are inputs to `Warehouse::create()`; `Warehouse::find()` fills only `id` and `name`. Other fields the provider returns, and the code Moloni create generates, are available through `getAdditionalData()`.

---

### TaxExemptionData

Represents a tax exemption reason stored in the provider.

```php
use CsarCrr\InvoicingIntegration\Data\TaxExemptionData;
```

| Property      | Type      | Description                                                   |
| ------------- | --------- | ------------------------------------------------------------- |
| `code`        | `?string` | Exemption reason code, e.g. `M04`, used as `taxExemptionReason` and `exemptionReason` |
| `description` | `?string` | Provider text describing the exemption reason                 |

Other fields the provider returns, such as the Cegid Vendus `law`, are available through `getAdditionalData()`.

---

### DocumentTypeData

Represents a document type stored in the provider.

```php
use CsarCrr\InvoicingIntegration\Data\DocumentTypeData;
```

| Property | Type      | Description                                      |
| -------- | --------- | ------------------------------------------------ |
| `code`   | `?string` | Document type code, e.g. `FT`                    |
| `name`   | `?string` | Name of the document type, e.g. `Fatura`         |

Other fields the provider returns, such as the Moloni `document_type_id`, are available through `getAdditionalData()`.

---

### PaymentMethodData

Represents a payment method stored in the provider.

```php
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

$paymentMethod = PaymentMethodData::make([
    'name' => 'Multibanco',
    'type' => PaymentMethod::MB,
]);
```

| Property | Type             | Description                                                         |
| -------- | ---------------- | ------------------------------------------------------------------- |
| `name`   | `string`         | Required. Name of the payment method                                |
| `type`   | `?PaymentMethod` | Kind of payment method, `null` when the provider has no match for it |
| `id`     | `?int`           | Provider-assigned payment method ID                                 |

---

### PaymentData

Payment records for invoice settlements.

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
use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\AddressData;
use CsarCrr\InvoicingIntegration\Data\TransportData;

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
| `country`    | `?string` | ISO 3166-1 alpha-2 code (validated); not sent when `null` |
| `date`       | `?Carbon` | Optional load/unload date           |
| `time`       | `?Carbon` | Optional load/unload time           |

---

### Invoice (Response)

The hydrated `InvoiceData` returned after issuing an invoice. Contains the provider's response data.

> [!NOTE]
> The `output` property on the returned `InvoiceData` is an `OutputData` instance (the same type used as the input DTO). It is hydrated with the provider's file content after `execute()`. See the [OutputData](#outputdata) section below.

**Properties:**

| Property    | Type      | Description                             | Notes                                   |
| ----------- | --------- | --------------------------------------- | --------------------------------------- |
| `id`        | `int`     | Provider's internal ID                  | -                                       |
| `sequence`  | `string`  | Invoice sequence (e.g., "FT 01P2025/1") | -                                       |
| `total`     | `int`     | Total amount in cents (gross)           | -                                       |
| `totalNet`  | `int`     | Net total amount in cents               | -                                       |
| `atcudHash` | `?string` | ATCUD hash (Portugal AT code)           | -                                       |
| `output`    | `OutputData` | Output for PDF/ESC/POS               | `content` is `null` when the provider omits the file |

---

### OutputData

Holds a document's file. Returned in `InvoiceData->output` by `Invoice::create()` and by `Invoice::pdf()->execute()->getOutput()`.

```php
use CsarCrr\InvoicingIntegration\Data\OutputData;
```

**Properties:**

| Property   | Type           | Default      | Description |
| ---------- | -------------- | ------------ | ----------- |
| `format`   | `OutputFormat` | `PDF_BASE64` | Output format. Set it on input to choose the format `Invoice::create()` requests |
| `content`  | `?string`      | `null`       | The file, base64 encoded |
| `fileName` | `?string`      | `null`       | File name used by `save()`. Cegid Vendus `Invoice::create()` sets it to the document number |
| `url`      | `?string`      | `null`       | Provider download link. Set by `Invoice::pdf()` on Moloni only |

**Methods:**

| Method                | Return Type | Description |
| --------------------- | ----------- | ----------- |
| `save(?string $path)` | `string`    | `PDF_BASE64`: decodes `content`, writes it to the `local` disk and returns the path. The path defaults to `invoices/{fileName}` (a random name when `fileName` is `null`), is sanitized and gets a `.pdf` suffix. `ESCPOS`: returns the decoded content and writes nothing. Returns `''` when `content` is `null` |

---

## Enums

Enums provide type-safe values for document types, payment methods, taxes, and output formats.

### InvoiceType

Document types.

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

### DueDateTerm

Payment terms for `InvoiceData->dueDate` and `ClientData->defaultPayDue`. The values are the ones Cegid Vendus accepts. An invoice is due today plus the term's days; `Days0` is today.

```php
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;
```

| Value    | Days |
| -------- | ---- |
| `Days0`  | 0    |
| `Days1`  | 1    |
| `Days7`  | 7    |
| `Days15` | 15   |
| `Days30` | 30   |
| `Days45` | 45   |
| `Days60` | 60   |
| `Days90` | 90   |

### PaymentMethod

Payment methods. Each method maps to a provider-specific ID configured in your `.env`.

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

Classification for invoice line items - products, services, or tax entries.

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

### Unit

`Unit` is the default implementation of the `ShouldBeUnit` contract, shipping with two common units out of the box.

```php
use CsarCrr\InvoicingIntegration\Enums\Unit;
```

| Case   | Value    |
| ------ | -------- |
| `KG`   | `'kg'`   |
| `UNIT` | `'unit'` |

### ShouldBeUnit

`ItemData->unit` accepts any enum that implements `ShouldBeUnit`, not just the built-in `Unit` enum. This lets you define your own set of units to match your provider configuration:

```php
use CsarCrr\InvoicingIntegration\Contracts\ShouldBeUnit;

enum MyUnit: string implements ShouldBeUnit
{
    case LITRE  = 'litre';
    case METER  = 'meter';
    case HOUR   = 'hour';
}
```

Each case's `value` must have a corresponding entry in your config's `units` map so the resolver can look up the provider ID:

```php
// config/invoicing-integration.php
'units' => [
    'litre' => env('CEGID_VENDUS_UNIT_LITRE_ID'),
    'meter' => env('CEGID_VENDUS_UNIT_METER_ID'),
    'hour'  => env('CEGID_VENDUS_UNIT_HOUR_ID'),
],
```

### ItemTax

VAT rates for invoice items. Use `EXEMPT` with an exemption reason code (see `TaxExemption::find()`) for tax-free items.

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

### OutputFormat

Output format for invoice documents - PDF for email/archive or ESC/POS for thermal printers.

```php
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
```

| Value        | Description                    |
| ------------ | ------------------------------ |
| `PDF_BASE64` | PDF document (base64 encoded)  |
| `ESCPOS`     | ESC/POS thermal printer format |

---

## Exceptions

Exceptions are thrown for validation errors (before the request) and provider errors (after the request).

### Validation Exceptions

These are thrown before the request is sent to the provider, during local validation.

| Exception                                         | When Thrown                                 |
| ------------------------------------------------- | ------------------------------------------- |
| `InvoiceRequiresClientVatException`               | Client provided with empty VAT or name but no VAT |
| `CreditNoteReasonIsMissingException`              | NC type without credit note reason          |
| `NeedsDateToSetLoadPointException`                | Transport without origin date               |
| `InvalidCountryException`                         | Invalid ISO country code                    |
| `UnsupportedQuantityException`                    | Item quantity is zero or negative           |
| `MissingRelatedDocumentException`                 | Credit note item without related document   |
| `ExemptionCanOnlyBeUsedWithExemptTaxException`    | Tax exemption set without `ItemTax::EXEMPT` |
| `ExemptionLawCanOnlyBeUsedWithExemptionException` | Exemption law set without exemption reason  |

### Provider Exceptions

These are thrown when communication with the provider fails or returns an error.

| Exception                         | When Thrown                                              |
| --------------------------------- | -------------------------------------------------------- |
| `RequestFailedException`          | Provider returned an error response                      |
| `UnauthorizedException`           | Invalid or missing API credentials (401)                 |
| `FailedReachingProviderException` | Provider is unreachable or returned 500                  |
| `CouldNotGetUnitIdException`      | Unit not found in config when creating a catalog item    |

---

For more details, see the source code in `src/` or the tests in `tests/Unit/Invoice/`.
