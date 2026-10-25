# Changelog

Changes that need action when upgrading.

## Unreleased

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
| Cegid Vendus | `due_date` = the date you passed | `due_date` = today + the term's days                                      |
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
| Moloni       | `payment_day` = the days you passed, `maturity_date_id` = `0` | `maturity_date_id` = the closest account due date (`Days15` when not set); `payment_day` is not sent |

See [Creating a Client](clients/creating-a-client.md) and [API Reference – DueDateTerm](api-reference.md#duedateterm).
