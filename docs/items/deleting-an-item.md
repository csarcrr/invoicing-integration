# Deleting an Item

Use `Item::delete()` to delete a stored item, by its provider ID.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Item;

Item::delete(ItemData::make(['id' => 12345]))->execute();
```

- Pass the provider item ID (`id`).
- `execute()` returns nothing. Check the outcome through the exceptions below.
- Without an `id`, `Item::delete()` throws `InvalidArgumentException` with `Item ID is required.`
- When the provider rejects the deletion, `execute()` throws `RequestFailedException`.

## Cegid Vendus

The request is sent to `DELETE products/{id}` with no body.

## Moloni

The request is sent to `products/delete` with `product_id`.

Moloni returns `valid` and `message`. When `valid` is not `1`, `execute()` throws `RequestFailedException` with the provider `message`.

## Reference

See also:

- [Items Overview](README.md)
- [Updating an Item](updating-an-item.md)
- [API Reference – ShouldDeleteItem](../api-reference.md#shoulddeleteitem-contract)
