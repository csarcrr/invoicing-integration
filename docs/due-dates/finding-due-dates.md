# Finding Due Dates

Use `DueDate::find()` to list the payment terms stored in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Facades\DueDate;

$results = DueDate::find()->execute();

foreach ($results->getList() as $dueDate) {
    $dueDate->id;
    $dueDate->name;
    $dueDate->days;
}
```

- `getList()` returns a `Collection` of `DueDateData` instances.
- `DueDate::find()` takes no filters: the provider returns every due date of the account.

## Pagination

`DueDate::find()` implements `next()`, `previous()` and `page()` through the `HasPaginator` trait.

```php
$results = DueDate::find()->execute();

while ($results->getList()->isNotEmpty()) {
    // do stuff

    $results->next()->execute();
}
```

- Each page holds up to 50 due dates.
- `getTotalPages()` returns `null`, because Moloni does not report a total: keep calling `next()` until a page comes back empty.
- `previous()` before page 1, or `page()` with a value below 1, raises `NoMorePagesException`.

## Moloni

The request is sent to `maturityDates/getAll` with `qty` and `offset`. Each row is mapped as:

| Moloni field       | `DueDateData` |
| ------------------ | ------------- |
| `maturity_date_id` | `id`          |
| `name`             | `name`        |
| `days`             | `days`        |

`associated_discount` is not mapped.

## Cegid Vendus

Not supported: `DueDate::find()` throws `OperationNotSupportedException`.

## Reference

See also:

- [Due Dates Overview](README.md)
- [Creating a Due Date](creating-a-due-date.md)
- [API Reference – ShouldFindDueDate](../api-reference.md#shouldfindduedate-contract)
