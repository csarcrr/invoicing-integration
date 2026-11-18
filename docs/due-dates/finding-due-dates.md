# Finding Due Dates

Use `DueDate::find()` to list the payment terms stored in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Facades\DueDate;

$results = DueDate::find()->execute();

foreach ($results->get() as $dueDate) {
    $dueDate->id;
    $dueDate->name;
    $dueDate->days;
}
```

- `get()` returns a `Collection` of `DueDateData` instances.
- `DueDate::find()` takes no filters: the provider returns every due date of the account.

## Pagination

`DueDate::find()` implements `next()`, `previous()` and `page()` through the `HasPaginator` trait.

On Moloni, keep calling `next()` until a page comes back empty:

```php
$results = DueDate::find()->execute();

while ($results->get()->isNotEmpty()) {
    // do stuff

    $results->next()->execute();
}
```

- Moloni: each page holds up to 50 due dates. `getTotalPages()` returns `null`, because Moloni does not report a total.
- Vendus: the provider returns every due date in one response, so `getTotalPages()` is `1` after `execute()`, and `next()` raises `NoMorePagesException`.
- `previous()` before page 1, or `page()` with a value below 1, raises `NoMorePagesException` on both providers.

## Moloni

The request is sent to `maturityDates/getAll` with `qty` and `offset`. Each row is mapped as:

| Moloni field       | `DueDateData` |
| ------------------ | ------------- |
| `maturity_date_id` | `id`          |
| `name`             | `name`        |
| `days`             | `days`        |

`associated_discount` is not mapped.

## Vendus

The request is sent to `GET documents/duedates`, with no parameters. The response is an object keyed by `now` or by a number of days, with the name as value. Each entry is mapped as:

| Vendus field                          | `DueDateData`                                         |
| ------------------------------------------- | ----------------------------------------------------- |
| value                                       | `name`                                                |
| key (`now` is `0`, otherwise the number)    | `days`                                                |
| key                                         | additional data `key`, the raw key (`now`, `30`)      |

Vendus has no due date ID, so `id` is `null`. Read the raw key with `getAdditionalData()['key']`. The rows keep the order the provider returns them.

## Reference

See also:

- [Due Dates Overview](README.md)
- [Creating a Due Date](creating-a-due-date.md)
- [API Reference – ShouldFindDueDate](../api-reference.md#shouldfindduedate-contract)
