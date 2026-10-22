# Product Stocks

Product stock is changed through stock movements. A movement adds (positive quantity) or removes (negative quantity) units of a product. Moloni calls them _movimentos de stock_.

## Quick example

```php
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;

$move = ProductStock::move(StockMovementData::make([
    'productId' => 1542,
    'quantity' => -5,
]))->execute();

echo $move->getMovement()->id;           // provider-assigned movement ID
echo $move->getProductStock()->quantity; // product stock after the movement
```

## Available operations

| Operation               | Docs                                              |
| ----------------------- | ------------------------------------------------- |
| Move stock              | [Moving Stock](moving-stock.md)                   |
| Get the stock           | [Getting Stock](getting-stock.md)                 |
| Delete a stock movement | [Deleting a Movement](deleting-a-movement.md)     |

## Provider support

| Operation       | Cegid Vendus | Moloni | Invoice Express |
| --------------- | ------------ | ------ | --------------- |
| Move Stock      | ❌           | ✅     | ❌              |
| Get Stock       | ❌           | ✅     | ❌              |
| Delete Movement | ❌           | ✅     | ❌              |

On Cegid Vendus every operation throws `OperationNotSupportedException`.
