# Changelog

Changes that need action when upgrading.

## Unreleased

### Added: `TaxExemption::find()`

`TaxExemption::find()` lists the tax exemption reasons stored in the provider, on Moloni and Cegid Vendus. Each `TaxExemptionData` has a `code` (e.g. `M04`) and a `description`.

See [Finding Tax Exemptions](taxes/finding-tax-exemptions.md).

### Added: `DocumentType::find()`

`DocumentType::find()` lists the document types stored in the provider, on Moloni and Cegid Vendus. Each `DocumentTypeData` has a `code` (e.g. `FT`) and a `name` (e.g. `Fatura`). On Moloni, pass a `LanguageData` to get the names in another language.

See [Finding Document Types](document-types/finding-document-types.md).

### Breaking: `TaxExemptionReason` enum and `laws()` removed

The `TaxExemptionReason` enum and its `laws()` helper are removed. `ItemData->taxExemptionReason` and `TaxData->exemptionReason` are now `?string` and take the provider's exemption code.

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. Code that uses `TaxExemptionReason` cases or `laws()` must be updated. The package sends the code as given and does not check it.

Before:

```php
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;

'taxExemptionReason' => TaxExemptionReason::M04,
'taxExemptionLaw' => TaxExemptionReason::M04->laws()[0],
```

After:

```php
'taxExemptionReason' => 'M04',
'taxExemptionLaw' => 'Artigo 13.º do CIVA',
```

Use `TaxExemption::find()` to get the codes, and write the law text yourself: on Cegid Vendus it is in `getAdditionalData()['law']`, and Moloni returns none.

See [Tax Exemptions](invoices/tax-exemption.md).

### Changed: Moloni client create and update send only the fields you set

Moloni `Client::create()` and `Client::update()` no longer guess values for fields you leave unset. Before, they sent `PT` as the country, `pt` as the language, `DueDateTerm::Days15` as the due date, and `0` for `discount`, `creditLimit`, `deliveryMethodId`, `payment_day`, `payment_method_id` and `salesman_id`. Now those fields are not sent. `Client::update()` no longer overwrites stored values with these defaults.

`ClientData` has three new fields for Moloni: `paymentMethodId` (`payment_method_id`), `paymentDay` (`payment_day`) and `salesmanId` (`salesman_id`). Cegid Vendus does not send them.

Moloni requires `paymentMethodId`, `country`, `language` and `defaultPayDue` on create, so set them when you create a client.

See [Creating a Client](clients/creating-a-client.md#moloni).

### Changed: Cegid Vendus returns the ATCUD as atcudHash

The ATCUD returned by Cegid Vendus was dropped, so `atcudHash` was missing from the created invoice. It is now set. Moloni does not return the ATCUD, so it stays `null`.

See [Using Invoice Data](invoices/using-invoice-data.md).

### Changed: Cegid Vendus sends only the client fields it accepts on documents

Cegid Vendus rejected every invoice with a client, because the client was sent with `email_notification` and with every other `ClientData` field you set. The client now sends only the fields Cegid Vendus accepts, and `emailNotification` is sent as `send_email`. Other `ClientData` fields, such as `defaultPayDue`, `discount`, `creditLimit`, `deliveryMethodId`, `status`, `date` and `language`, are not sent.

See [Creating an Invoice – Cegid Vendus](invoices/creating-an-invoice.md#cegid-vendus).

### Changed: Cegid Vendus sends the item id and name

Cegid Vendus ignored the item `id` and `name`. An item identified only by `id` failed, and an item with a new `reference` failed because Cegid Vendus needs a title to create the product. `id` is now sent as `id` and `name` as `title`.

See [Creating an Invoice – Cegid Vendus](invoices/creating-an-invoice.md#cegid-vendus).

### Changed: Cegid Vendus sends item notes, percentage discounts and due dates

Cegid Vendus rejected invoices with an item `note`, an item `percentageDiscount` or a `dueDate`, because they were sent as `note`, `discount_percent` and `due_date`. They are now sent as `text`, `discount_percentage` and `date_due`.

See [Creating an Invoice – Cegid Vendus](invoices/creating-an-invoice.md#cegid-vendus).

### Changed: Cegid Vendus sends the configured mode

`CEGID_VENDUS_MODE` is now sent as `mode` when it is set. Before, it was ignored and documents always used the register's mode.

> [!WARNING]
> This affects **Cegid Vendus**. The valid values are `normal` and `tests`. Users who set `production`, as the docs said, get their documents rejected until they set `normal`. Users who set `tests` now really issue test documents.

See [Cegid Vendus configuration](providers/cegid-vendus/configuration.md#mode).

### Changed: Cegid Vendus receipts take the invoice number as `relatedDocument`

Cegid Vendus rejected every receipt (RG) with `Param invoices missing`, because `relatedDocument` was sent as `related_document_id`. It is now sent as the `document_number` of the invoice in `invoices`, so it must be the invoice number, not its `id`.

> [!WARNING]
> This affects **Cegid Vendus** receipts. Pass the invoice `sequence` (e.g. `FT 01P2025/1`) as `relatedDocument`. An `id` is sent as an invoice number, so it does not identify the invoice. Moloni still takes the invoice `id`.

See [Creating a Receipt (RG)](invoices/creating-a-RG-for-an-invoice.md).

### Changed: `AddressData` has no default country

`AddressData::country` defaulted to `PT`, so every transport address was sent with a country even when you set none. It is now `null` by default, and an address without a country is sent without one.

> [!WARNING]
> This affects **Cegid Vendus** transport details. Set `country` on the origin and destination addresses (e.g. `'country' => 'PT'`) to keep sending it.

See [Creating an Invoice – Transport Details](invoices/creating-an-invoice.md#transport-details).

### Changed: Cegid Vendus `Client::find()` returns an empty list when nothing matches

Cegid Vendus `Client::find()` no longer throws `RequestFailedException` when no client matches. It returns an empty list, as `Item::find()` does and as Moloni already did.

### Changed: Cegid Vendus `Client::create()` sends only the fields you set

Cegid Vendus `Client::create()` no longer sends the fields you did not set. Before, they were sent as empty objects, and Cegid Vendus stored `Array` in `address`, `city`, `postalcode`, `phone` and `email`, and `Ar` in `country`. `irsRetention` and `emailNotification` are sent only when you set them. Before, leaving them out sent `yes`, so IRS retention and email sending were switched on.

> [!WARNING]
> This affects **Cegid Vendus**. A client created without `irsRetention` or `emailNotification` now has both switched off. Set them to `true` to switch them on.

See [Creating a Client](clients/creating-a-client.md#cegid-vendus).

### Changed: Cegid Vendus `Item::find()` lists disabled items and returns an empty list when nothing matches

Cegid Vendus `Item::find()` sends `status=all`, so disabled items are listed too, with `enabled` set to `false`. Before, only enabled items were listed. A search that matches nothing (`404` with error code `A001`) returns an empty list instead of throwing `RequestFailedException` with `A001 - No data`. Any other `404` still throws. A `category` id on the filters is now sent as `category_id` and narrows the list; before, it was ignored.

> [!WARNING]
> This affects **Cegid Vendus**. Code that expects only enabled items from `find()` must filter on `enabled`. Code that catches `RequestFailedException` to detect an empty search must check for an empty list.

See [Finding Items](items/finding-an-item.md#cegid-vendus).

### Changed: `Item::create()` returns the created item

`Item::create()->execute()->getItem()` no longer returns the `ItemData` you passed with the new `id`. It returns the created item, mapped like `Item::get()`: from the create response on Cegid Vendus, and from a `products/getOne` request after the insert on Moloni. Fields you did not set hold the provider's values, such as the generated `reference` and the default `tax` on Cegid Vendus. On Moloni, `taxExemptionLaw` is `null`.

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. Code that expects the fields it did not set to be `null` after a create now reads the provider's values.

See [Creating an Item](items/creating-an-item.md#accessing-the-result).

### Changed: read items map `unit`

`Item::get()`, `Item::find()`, `Item::update()` and `Item::create()` set `unit` from the provider's `unit_id`, matched against the configured `units`. It is a `Unit` case, or `null` when no configured unit matches or the matching key is a custom unit. `unit_id` is no longer in `getAdditionalData()` on Moloni.

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. A created item with a custom `ShouldBeUnit` reads back `unit` as `null`.

### Changed: `Item::update()` returns the updated item

`Item::update()->execute()->getItem()` no longer returns the `ItemData` you passed. It returns the full updated item, mapped like `Item::get()`: from the response on Cegid Vendus, and from the current product with your fields merged over it on Moloni.

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. Code that expects the fields it did not set to be `null` after an update now reads the provider's values.

See [Updating an Item](items/updating-an-item.md#return-value).

### Changed: `ItemData` no longer has default values

`ItemData` built without `quantity`, `type`, `controlStock` or `enabled` now reads `null` in those properties, instead of `1`, `ItemType::Product`, `true` and `true`. `controlStock` and `enabled` are now `?bool`. Provider payloads for item creation and invoices are unchanged.

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. Code that reads those properties from an `ItemData` it built must handle `null`.

### Changed: items read `enabled` from the provider

`Item::get()`, `Item::find()`, `Item::update()` and `Item::create()` return `enabled` from the provider instead of a fixed `true`: Cegid Vendus `status` (`on` is `true`, `off` is `false`) and Moloni `visibility_id` (`1` is `true`, `0` is `false`). Moloni create sends `enabled` as `visibility_id`. `enabled` is `null` when the provider sends neither field. The top-level `quantity` of a read item is now `null` instead of `1`.

Moloni `get`, `find` and the `getOne` call of `update` send `with_invisible=1`, so products with `visibility_id` `0` are returned too. `update` sends the current `visibility_id` back, so a hidden product stays hidden.

Moloni `Item::update()` now accepts `enabled` and sends it as `visibility_id`, instead of throwing `OperationNotSupportedException`. Moloni does not document this parameter; see [Updating an Item](items/updating-an-item.md#moloni-behaviour).

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. Code that relied on read items always being `enabled` must handle `false` and `null`.

### Breaking: `UnsupportedQuantityException` moved

`UnsupportedQuantityException` moved from `Exceptions\Invoice\Items` to `Exceptions\Items`, since it is now also thrown by `StockMovementData`.

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. Code that catches the exception by its old class name must update the import.

Before:

```php
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\UnsupportedQuantityException;
```

After:

```php
use CsarCrr\InvoicingIntegration\Exceptions\Items\UnsupportedQuantityException;
```

### Breaking: unmapped provider fields are no longer serialized

`toArray()`, `toJson()` and JSON responses of `InvoiceData`, `ItemData`, `ClientData`, `DueDateData` and `PaymentMethodData` no longer include unmapped provider response fields. Read them with `getAdditionalData()`, which is unchanged.

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. Code that reads provider fields from the top level of `toArray()` (e.g. `$client->toArray()['status']`) must read them from `getAdditionalData()` instead.

### Breaking: `InvoiceData->dueDate` is now a `DueDateTerm`

`dueDate` no longer accepts a `Carbon` date. It takes a `DueDateTerm` enum: `Days0`, `Days1`, `Days7`, `Days15`, `Days30`, `Days45`, `Days60` or `Days90`. The due date sent to the provider is today plus the term's days.

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. Existing Cegid Vendus integrations that pass a `Carbon` date to `dueDate` stop working: `InvoiceData::make()` throws a `ValidationException` until the value is changed to a `DueDateTerm`.

Before:

```php
use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;

$invoiceData = InvoiceData::make([
    'dueDate' => Carbon::now()->addDays(30),
]);
```

After:

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;

$invoiceData = InvoiceData::make([
    'dueDate' => DueDateTerm::Days30,
]);
```

What changes per provider:

| Provider     | Before                           | Now                                                                       |
| ------------ | -------------------------------- | ------------------------------------------------------------------------- |
| Cegid Vendus | `due_date` = the date you passed | `date_due` = today + the term's days                                      |
| Moloni       | `expiration_date` = the date you passed | `expiration_date` = today + the term's days, plus `maturity_date_id` |

Arbitrary due dates are no longer possible: only the eight terms above are supported.

See [Creating an Invoice – Due Date](invoices/creating-an-invoice.md#due-date) and [API Reference – DueDateTerm](api-reference.md#duedateterm).

### Breaking: `ClientData->defaultPayDue` is now a `DueDateTerm`

`defaultPayDue` no longer accepts an `int`. It takes the same `DueDateTerm` enum as `InvoiceData->dueDate`. `ClientData::make()` throws a `ValidationException` for a number of days that is not a term.

> [!WARNING]
> This affects **Cegid Vendus** as well as Moloni. Code that passes an `int` to `defaultPayDue`, or that reads `$client->defaultPayDue` as an `int` after `Client::get()` or `Client::find()`, must use `DueDateTerm` instead.

Before:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;

$clientData = ClientData::make([
    'defaultPayDue' => 30,
]);
```

After:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;

$clientData = ClientData::make([
    'defaultPayDue' => DueDateTerm::Days30,
]);
```

What changes per provider:

| Provider     | Before                                          | Now                                                                |
| ------------ | ----------------------------------------------- | ------------------------------------------------------------------ |
| Cegid Vendus | `default_pay_due` = the days you passed (`0` → `now`) | Same, but only for the term's days (`Days0` → `now`)          |
| Moloni       | `payment_day` = the days you passed, `maturity_date_id` = `0` | `maturity_date_id` = the closest account due date (`Days15` when not set); `payment_day` = `0` |

See [Creating a Client](clients/creating-a-client.md) and [API Reference – DueDateTerm](api-reference.md#duedateterm).
