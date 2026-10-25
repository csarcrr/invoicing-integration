# Item Stocks

Item stock is changed through stock movements. A movement adds (positive quantity) or removes (negative quantity) units of an item. Moloni calls them _movimentos de stock_.

## Quick example

```php
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;

$move = ItemStock::move(StockMovementData::make([
    'itemId' => 1542,
    'quantity' => -5,
]))->execute();

echo $move->getMovement()->id;           // provider-assigned movement ID
```

## Available operations

| Operation               | Docs                                              |
| ----------------------- | ------------------------------------------------- |
| Move stock              | [Moving Stock](moving-stock.md)                   |

## Provider support

| Operation       | Cegid Vendus | Moloni | Invoice Express |
| --------------- | ------------ | ------ | --------------- |
| Move Stock      | ✅           | ✅     | ❌              |

To read the current stock, use `Item::get()`. See [Getting an Item](../items/getting-an-item.md#stock).
