# Getting Stock

Use `ProductStock::get()` to read the current stock of a product.

## Example

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;

$stock = ProductStock::get(ItemData::make(['id' => 1542]))
    ->execute()
    ->getProductStock();

$stock->productId; // 1542
$stock->quantity;  // total stock of the product
```

`getPayload()` and `execute()` throw `InvalidArgumentException` (`Product ID is required.`) when the `ItemData` has no `id`.

## ProductStockData

| Property    | Type        | Description                 |
| ----------- | ----------- | --------------------------- |
| `productId` | `int`       | ID of the product           |
| `quantity`  | `int\|float` | Total stock of the product |

## Moloni

The product is read from `products/getOne` with `product_id`. `quantity` is the `stock` field of the response: the total over all warehouses.

If the response has no `stock` field, `ProductStockData::make()` throws a `ValidationException`.

## Cegid Vendus

Not supported: `ProductStock::get()` throws `OperationNotSupportedException`.
