# Updating a Movement

Use `ProductStock::update()` to change an existing stock movement. After the update, the product is fetched again and its stock is returned.

## Example

```php
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;

$update = ProductStock::update(StockMovementData::make([
    'id' => 777,
    'productId' => 1542,
    'quantity' => 10,
    'notes' => 'Stock recount',
]))->execute();

$update->getMovement();              // the updated movement
$update->getProductStock()->quantity; // product stock after the update
```

## Fields

Uses `StockMovementData` (see [Moving Stock](moving-stock.md#available-fields)).

- `id` (the movement ID) and `productId` are required. `getPayload()` and `execute()` throw `InvalidArgumentException` (`Movement ID is required.` or `Product ID is required.`) when one is missing.
- `quantity`, `date`, `warehouseId` and `notes` are only sent when set.

## Moloni

The update is sent to `productStocks/update`, with `id` as `product_stock_id` and the other fields mapped as in [Moving Stock](moving-stock.md#moloni). `quantity` is sent as given, floats included. The movement `id` is kept as passed; it is not read from the response. The product stock is then read from `products/getOne`.

> [!WARNING]
> Moloni does not allow updating movements linked to documents (for example, created by an invoice). The request fails with `RequestFailedException`.

## Cegid Vendus

Not supported: `ProductStock::update()` throws `OperationNotSupportedException`.
