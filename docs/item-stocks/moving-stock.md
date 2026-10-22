# Moving Stock

Use `ItemStock::move()` to add or remove stock of an item.

## Example

```php
use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;

$movement = StockMovementData::make([
    'itemId' => 1542,
    'quantity' => -5,
    'date' => Carbon::now(),
    'warehouseId' => 3,
    'notes' => 'Broken units',
]);

$move = ItemStock::move($movement)->execute();

$move->getMovement()->id; // provider-assigned movement ID
```

## Available fields

| Field         | Type              | Default | Description                                                   |
| ------------- | ----------------- | ------- | ------------------------------------------------------------- |
| `itemId`   | `?int`            | `null`  | Required. ID of the item to move                           |
| `quantity`    | `null\|int\|float` | `null`  | Required. Units to add (positive) or remove (negative)        |
| `date`        | `?Carbon`         | `null`  | Movement date and time; the current time when `null`          |
| `warehouseId` | `?int`            | `null`  | Warehouse of the movement; the account's default when `null` |
| `notes`       | `?string`         | `null`  | Notes for the movement                                        |
| `id`          | `?int`            | `null`  | Provider-assigned movement ID, filled after the move          |

- `StockMovementData::make()` throws `UnsupportedQuantityException` when `quantity` is `0`.
- `getPayload()` and `execute()` throw `InvalidArgumentException` (`Item ID is required.` or `Quantity is required.`) when `itemId` or `quantity` is missing.

## Moloni

The movement is sent to `productStocks/insert`:

| `StockMovementData` | Moloni field                             |
| ---------------------- | ---------------------------------------- |
| `itemId`            | `product_id`                             |
| `quantity`             | `qty`                                    |
| `date`                 | `movement_date` (`Y-m-d H:i:s`)          |
| `warehouseId`          | `warehouse_id` (only sent when set)      |
| `notes`                | `notes` (only sent when set)             |

The returned `product_stock_id` is set as `id`. To read the stock after the movement, use `Item::get()`. See [Getting an Item](../items/getting-an-item.md#stock).

## Cegid Vendus

Not supported: `ItemStock::move()` throws `OperationNotSupportedException`.

## Error handling

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;

try {
    $move = ItemStock::move($movement)->execute();
} catch (RequestFailedException $e) {
    Log::error('Stock movement failed', ['message' => $e->getMessage()]);
}
```

For a full list of exceptions, see [Handling Errors](../handling-errors.md).
