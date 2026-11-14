# Getting an Item

Retrieve an existing item from your provider's catalog using `Item::get()`.

## Basic example

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Item;

// Create an ItemData object with the known provider ID
$itemData = ItemData::make(['id' => 123]);

// Fetch the item from the provider
$item = Item::get($itemData)->execute()->getItem();

// Access item data via public properties or $item->toArray()
$item->id;          // 123
$item->name;        // Item name
$item->description; // Longer description
$item->reference;   // Product SKU / code
$item->barcode;     // EAN or barcode identifier
```

## Requirements

The `id` property is **required** when retrieving an item:

```php
// This will throw an InvalidArgumentException
$itemData = ItemData::make([]);
Item::get($itemData)->execute(); // Error: Item ID is required.

// Correct usage
$itemData = ItemData::make(['id' => 123]);
Item::get($itemData)->execute()->getItem(); // Works
```

## Retrieved properties

After calling `execute()->getItem()`, the `ItemData` object is populated with the data returned by the provider:

```php
$itemData = ItemData::make(['id' => 123]);
$item = Item::get($itemData)->execute()->getItem();

$item->id;          // 123
$item->name;        // Product name
$item->description; // Product description
$item->reference;   // Product SKU / code
$item->barcode;     // EAN or barcode identifier
$item->category;    // CategoryData with the provider's category id
$item->controlStock; // Whether the provider tracks stock for this item
$item->enabled;     // Whether the item is active in the provider
$item->type;        // ItemType
$item->tax;         // ItemTax
$item->price;       // Gross price in cents
```

| Property       | Cegid Vendus field | Moloni field                         |
| -------------- | ------------------ | ------------------------------------ |
| `category`     | `category_id`      | `category_id`                        |
| `controlStock` | `stock.control`    | `has_stock`                          |
| `enabled`      | `status` (`on`/`off`) | `visibility_id` (`1`/`0`)         |
| `unit`         | `unit_id`          | `unit_id`                            |
| `type`         | `type_id`          | `type`                               |
| `tax`          | `tax.id`           | `taxes[0].tax_id`, see below         |
| `taxExemptionReason` | `tax.exemption` | `exemption_reason`              |
| `taxExemptionLaw` | `tax.exemption_law` | Not returned                       |
| `price`        | `prices.gross`     | `price` plus `taxes[0].value`        |

On Moloni, `tax` is the `ItemTax` whose `MOLONI_TAX_*_ID` matches `taxes[0].tax_id`, so a configured tax with no VAT type, such as a 0% exempt tax, still maps to its `ItemTax`. A tax that is not configured falls back to `taxes[0].tax.vat_type`, and is `null` when that is not an `ItemTax` value.

`price` and `tax` are `null` when the provider sends no price or tax data. `taxExemptionReason` and `taxExemptionLaw` are only set when `tax` is `ItemTax::EXEMPT`. `taxExemptionReason` is the provider's code as given, and is `null` when the provider sends none. `type` is `null` when the provider sends an unknown type. `enabled` is `null` when the provider sends no status or visibility. `category` is always a `CategoryData`. Its `id` is the provider's `category_id`, and is `null` when the provider sends none. Cegid Vendus sends `category_id` `0` for an item with no category, so `id` is `0`. `unit` is the `Unit` case whose configured unit id matches `unit_id`, and is `null` when no configured unit matches or the key is a custom unit.

On Moloni, the request sends `with_invisible=1`, so products with `visibility_id` `0` are returned too.

### Stock

`stock` is a `Collection` of `ItemStockData`, one entry per warehouse:

```php
$item = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

foreach ($item->stock as $stock) {
    $stock->itemId;      // 123
    $stock->warehouseId; // warehouse of this entry
    $stock->quantity;    // stock in that warehouse
}

$item->stock->sum('quantity'); // total stock
```

| Provider     | Response field | `warehouseId` from | `quantity` from |
| ------------ | -------------- | ------------------ | --------------- |
| Moloni       | `warehouses[]` | `warehouse_id`     | `stock`         |
| Cegid Vendus | `stock.stores[]` | `store_id`       | `stock`         |

When the response has no stock entries, `stock` is an empty `Collection`.

### Additional response data

Provider response fields that the package does not handle are stored as additional data on the returned DTO and are accessible via `getAdditionalData()`. For Cegid Vendus, that is any field outside the provider's supported item properties. The `prices`, `tax` and `stock` objects are mapped, so they are not in additional data:

```php
$item = Item::get($itemData)->execute()->getItem();

$item->getAdditionalData();
// [
//     'lot_control' => '1',
// ]
```

> [!NOTE]
> Fields that the package explicitly maps (`name`, `description`, `reference`, `barcode`, `category`, `controlStock`, `enabled`, `type`, `tax`, `price`) are never duplicated in `getAdditionalData()`.

## Complete example

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Item;

// Assume you stored the provider ID when the item was first created
$storedItemId = 123;

$itemData = ItemData::make(['id' => $storedItemId]);
$item = Item::get($itemData)->execute()->getItem();

// Use the mapped fields directly
echo $item->name;        // e.g. "Widget Pro"
echo $item->description; // e.g. "High-quality widget for professional use"
echo $item->reference;   // e.g. "WGT-001"

// Access provider-specific fields via additional data
$additionalData = $item->getAdditionalData();
```

## Error handling

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;
use CsarCrr\InvoicingIntegration\Facades\Item;

try {
    $itemData = ItemData::make(['id' => 123]);
    $item = Item::get($itemData)->execute()->getItem();
} catch (InvalidArgumentException $e) {
    // Item ID was not provided
} catch (UnauthorizedException $e) {
    // Invalid API credentials
} catch (RequestFailedException $e) {
    // Item not found or provider error
}
```

An unknown `id` throws `RequestFailedException` on both providers. Moloni answers an unknown `product_id` with an empty response instead of an error, so the package throws `RequestFailedException` with the message `Item not found.`

For a full list of exceptions, see [Handling Errors](../handling-errors.md).

---

Always store the provider-assigned `id` when creating items for later retrieval. Use retrieved items to verify catalog state before referencing them in invoices.

---

Back to: [Items Overview](README.md)
