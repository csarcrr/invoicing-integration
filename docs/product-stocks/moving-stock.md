# Moving Stock

Use `ProductStock::move()` to add or remove stock of a product. After the movement is created, the product is fetched again and its stock is returned.

## Example

```php
use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;

$movement = StockMovementData::make([
    'productId' => 1542,
    'quantity' => -5,
    'date' => Carbon::now(),
    'warehouseId' => 3,
    'notes' => 'Broken units',
]);

$move = ProductStock::move($movement)->execute();

$move->getMovement()->id;           // provider-assigned movement ID
$move->getProductStock()->quantity; // product stock after the movement
```

## Available fields

| Field         | Type              | Default | Description                                                   |
| ------------- | ----------------- | ------- | ------------------------------------------------------------- |
| `productId`   | `?int`            | `null`  | Required. ID of the product to move                           |
| `quantity`    | `null\|int\|float` | `null`  | Required. Units to add (positive) or remove (negative)        |
| `date`        | `?Carbon`         | `null`  | Movement date and time; the current time when `null`          |
| `warehouseId` | `?int`            | `null`  | Warehouse of the movement; the account's default when `null` |
| `notes`       | `?string`         | `null`  | Notes for the movement                                        |
| `id`          | `?int`            | `null`  | Provider-assigned movement ID, filled after the move          |

- `StockMovementData::make()` throws `UnsupportedQuantityException` when `quantity` is `0`.
- `getPayload()` and `execute()` throw `InvalidArgumentException` (`Product ID is required.` or `Quantity is required.`) when `productId` or `quantity` is missing.

## Moloni

The movement is sent to `productStocks/insert`:

| `StockMovementData` | Moloni field                             |
| ---------------------- | ---------------------------------------- |
| `productId`            | `product_id`                             |
| `quantity`             | `qty`                                    |
| `date`                 | `movement_date` (`Y-m-d H:i:s`)          |
| `warehouseId`          | `warehouse_id` (only sent when set)      |
| `notes`                | `notes` (only sent when set)             |

The returned `product_stock_id` is set as `id`. The product stock is then read from `products/getOne` (`stock` field). See [Getting Stock](getting-stock.md).

## Cegid Vendus

Not supported: `ProductStock::move()` throws `OperationNotSupportedException`.

## Error handling

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;

try {
    $move = ProductStock::move($movement)->execute();
} catch (RequestFailedException $e) {
    Log::error('Stock movement failed', ['message' => $e->getMessage()]);
}
```

For a full list of exceptions, see [Handling Errors](../handling-errors.md).
