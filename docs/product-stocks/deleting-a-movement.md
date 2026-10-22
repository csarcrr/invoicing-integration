# Deleting a Movement

Use `ProductStock::delete()` to remove a stock movement.

## Example

```php
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;

ProductStock::delete(StockMovementData::make(['id' => 777]))->execute();
```

Only `id` (the movement ID) is used. `getPayload()` and `execute()` throw `InvalidArgumentException` (`Movement ID is required.`) when it is missing. The product stock is not fetched after a delete; use [Getting Stock](getting-stock.md) to read it.

## Moloni

The request is sent to `productStocks/delete` with `id` as `product_stock_id`.

> [!WARNING]
> Moloni only deletes movements added manually or through the API. Movements linked to documents cannot be deleted; the request fails with `RequestFailedException`.

## Cegid Vendus

Not supported: `ProductStock::delete()` throws `OperationNotSupportedException`.
