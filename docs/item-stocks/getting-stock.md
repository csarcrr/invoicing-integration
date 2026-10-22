# Getting Stock

Use `ItemStock::get()` to read the current stock of an item.

## Example

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;

$stock = ItemStock::get(ItemData::make(['id' => 1542]))
    ->execute()
    ->getItemStock();

$stock->itemId; // 1542
$stock->quantity;  // total stock of the item
```

`getPayload()` and `execute()` throw `InvalidArgumentException` (`Item ID is required.`) when the `ItemData` has no `id`.

## ItemStockData

| Property    | Type        | Description                 |
| ----------- | ----------- | --------------------------- |
| `itemId` | `int`       | ID of the item           |
| `quantity`  | `int\|float` | Total stock of the item |

## Moloni

The item is read from `products/getOne` with `product_id`. `quantity` is the `stock` field of the response: the total over all warehouses.

If the response has no `stock` field, `ItemStockData::make()` throws a `ValidationException`.

## Cegid Vendus

Not supported: `ItemStock::get()` throws `OperationNotSupportedException`.
