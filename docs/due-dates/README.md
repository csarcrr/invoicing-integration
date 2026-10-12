# Due Dates

A due date is a reusable payment term stored in the provider: a name and a number of days (for example "30 dias"). Moloni calls them maturity dates (_prazos de vencimento_).

> [!NOTE]
> This is not the same as `InvoiceData->dueDate`, which is the `DueDateTerm` (number of days) a single invoice is due in. A `DueDateData` is an account-level payment term you can manage through the `DueDate` facade. On Moloni, an invoice with a `dueDate` is linked to the account due date with the same or closest number of days.

## Quick example

```php
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Facades\DueDate;

$dueDate = DueDate::create(DueDateData::make([
    'name' => '30 dias',
    'days' => 30,
]))->execute()->getDueDate();

echo $dueDate->id; // provider-assigned ID
```

## Available operations

| Operation         | Docs                                          |
| ----------------- | --------------------------------------------- |
| Create a due date | [Creating a Due Date](creating-a-due-date.md) |
| Find due dates    | [Finding Due Dates](finding-due-dates.md)     |

## Provider support

| Operation       | Cegid Vendus | Moloni | Invoice Express |
| --------------- | ------------ | ------ | --------------- |
| Create Due Date | ❌           | ✅     | ❌              |
| Find Due Dates  | ❌           | ✅     | ❌              |

On Cegid Vendus both operations throw `OperationNotSupportedException`.
