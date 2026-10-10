# Creating a Due Date

Use `DueDate::create()` to register a payment term in the provider.

## Example

```php
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Facades\DueDate;

$dueDate = DueDateData::make([
    'name' => '30 dias',
    'days' => 30,
]);

$created = DueDate::create($dueDate)->execute()->getDueDate();

echo $created->id; // provider-assigned ID
```

## Available fields

| Field  | Type     | Default | Description                               |
| ------ | -------- | ------- | ----------------------------------------- |
| `name` | `string` | —       | Required. Name of the payment term        |
| `days` | `int`    | —       | Required. Number of days until payment    |
| `id`   | `?int`   | `null`  | Provider-assigned ID, filled after create |

`DueDateData::make()` throws a `ValidationException` when `name` or `days` is missing.

## Moloni

The request is sent to `maturityDates/insert`:

| `DueDateData` | Moloni field |
| ------------- | ------------ |
| `name`        | `name`       |
| `days`        | `days`       |

`associated_discount` is always sent as `0`.

The returned `maturity_date_id` is set as `id`. Other response fields are available through `getAdditionalData()`.

## Cegid Vendus

Not supported: `DueDate::create()` throws `OperationNotSupportedException`.

## Error handling

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;

try {
    $created = DueDate::create($dueDate)->execute()->getDueDate();
} catch (RequestFailedException $e) {
    Log::error('Due date creation failed', ['message' => $e->getMessage()]);
}
```

For a full list of exceptions, see [Handling Errors](../handling-errors.md).
