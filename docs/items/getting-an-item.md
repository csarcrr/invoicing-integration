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
```

### Additional response data

The provider response typically includes fields beyond what the package maps onto `ItemData` (e.g., `reference`, `barcode`, `gross_price`, `tax_id`). Those fields are stored as additional data on the returned DTO and are accessible via `getAdditionalData()`:

```php
$item = Item::get($itemData)->execute()->getItem();

$item->getAdditionalData();
// [
//     'reference'          => 'reference-1',
//     'barcode'            => 'barcode-1',
//     'gross_price'        => 20.0,
//     'unit_id'            => 19999,
//     'type_id'            => 'P',
//     'stock_control'      => '1',
//     'tax_id'             => 'ISE',
//     'tax_exemption'      => 'M40',
//     'tax_exemption_law'  => 'Artigo 6.º n.º 6 alínea a) do CIVA, a contrário',
//     'category_id'        => 1,
//     'status'             => 'on',
//     'lot_control'        => '1',
// ]
```

> [!NOTE]
> Fields that the package explicitly maps (`name`, `description`) are never duplicated in `getAdditionalData()`. Only provider fields without a first-class mapping on `ItemData` appear there.

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

// Access provider-specific fields via additional data
$additionalData = $item->getAdditionalData();
$grossPrice = $additionalData['gross_price'] ?? null; // e.g. 29.99
$taxId      = $additionalData['tax_id'] ?? null;      // e.g. "NOR"
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

For a full list of exceptions, see [Handling Errors](../handling-errors.md).

---

Always store the provider-assigned `id` when creating items for later retrieval. Use retrieved items to verify catalog state before referencing them in invoices.

---

Back to: [Items Overview](README.md)
