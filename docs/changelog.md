# Changelog

Changes that need action when upgrading.

## Unreleased

### Breaking: `InvoiceData->dueDate` is now a `DueDateTerm`

`dueDate` no longer accepts a `Carbon` date. It takes a `DueDateTerm` enum: `Days0`, `Days10`, `Days15`, `Days30`, `Days60`, `Days90` or `Days120`. The due date sent to the provider is today plus the term's days.

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

Arbitrary due dates are no longer possible: only the six terms above are supported.

See [Creating an Invoice – Due Date](invoices/creating-an-invoice.md#due-date) and [API Reference – DueDateTerm](api-reference.md#duedateterm).
