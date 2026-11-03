# Updating an Item

Use `Item::update()` to change an existing product or service in the provider's catalog. The update is partial: only the fields you set change.

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Item;

$item = Item::update(ItemData::make([
    'id'          => 1542,
    'name'        => 'Widget Pro 2',
    'description' => '',
    'price'       => 3499,
]))->execute()->getItem();
```

## Field values

| Value            | Meaning             |
| ---------------- | ------------------- |
| `null` / omitted | Leave the field alone |
| `''`             | Clear the field     |
| `0`, `false`     | Sent as given       |

`id` is required. Without it, `execute()` throws `InvalidArgumentException` before any request.

## Return value

`getItem()` returns the updated item, mapped like [`Item::get()`](getting-an-item.md). Unhandled fields from the update response are in `getAdditionalData()`.

- **Cegid Vendus:** the item is mapped from the `PATCH` response, which is the full product.
- **Moloni:** `products/update` only returns the product id, so the item is the product read with `products/getOne` before the update, with the fields you set merged over it. A tax other than `EXEMPT` clears `taxExemptionReason`, and a new `tax` without `price` keeps the gross price. It is what was sent, not re-read from Moloni, so the net price Moloni stores can round the gross `price` of a later `Item::get()` by a cent.

## Stock

Stock quantities cannot change here: `stock` and `quantity` are ignored. Use [Moving Stock](../item-stocks/moving-stock.md). `controlStock` can change.

## Tax exemption

`ItemData` validation still applies. To change an exemption reason, also pass `tax: ItemTax::EXEMPT`.

## Field mapping

| `ItemData` field     | Cegid Vendus        | Moloni                    |
| -------------------- | ------------------- | ------------------------- |
| `id`                 | URL `products/{id}` | `product_id`              |
| `name`               | `title`             | `name`                    |
| `description`        | `description`       | `summary`                 |
| `reference`          | `reference`         | `reference`               |
| `barcode`            | `barcode`           | `ean`                     |
| `price`              | `gross_price`       | `price` (net)             |
| `type`               | `type_id`           | `type`                    |
| `tax`                | `tax_id`            | `taxes`                   |
| `taxExemptionReason` | `tax_exemption`     | `exemption_reason`        |
| `taxExemptionLaw`    | `tax_exemption_law` | Ignored                   |
| `category`           | `category_id`       | `category_id`             |
| `unit`               | `unit_id`           | `unit_id`                 |
| `controlStock`       | `stock_control`     | `has_stock`               |
| `enabled`            | `status`            | `visibility_id`, see below |

An unmapped `unit` throws `CouldNotGetUnitIdException`.

## Moloni behaviour

Moloni only replaces whole products, so an update makes two requests: `products/getOne` with `with_invisible=1`, then `products/update` with your fields merged over the current values. Stock and warehouses are not sent.

- **`enabled`:** sent as `visibility_id`, `1` for `true` and `0` for `false`. When `enabled` is not set, the current `visibility_id` is sent back unchanged, so a hidden product stays hidden.

> [!WARNING]
> Moloni does not document `visibility_id` as a `products/update` parameter. The package relies on Moloni accepting it, which was confirmed against the API, but Moloni can change this without notice.
- **`price`:** stays gross in `ItemData`. The net price is calculated with the new tax, or with the current tax when `tax` is not set.
- **`tax` without `price`:** the gross price stays the same and the net price is recalculated. `ItemTax::EXEMPT` replaces the tax with the configured 0% exempt tax.
- **`tax` other than `EXEMPT`:** clears the exemption reason, unless you set a new one. An unconfigured tax throws `CouldNotGetTaxIdException` before any request.
- **Products with several taxes:** the gross price is derived from the first tax only.
