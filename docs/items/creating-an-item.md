# Creating an Item

Use `Item::create()` to register a product or service in the provider's catalog.

## Basic example

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Facades\Item;

$item = ItemData::make([
    'name'  => 'Widget Pro',
    'type'  => ItemType::Product,
    'price' => 5000, // €50.00 (in cents)
]);

$created = Item::create($item)->execute()->getItem();

echo $created->id; // provider-assigned ID
```

## Complete example

```php
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Unit;
use CsarCrr\InvoicingIntegration\Facades\Item;

$item = ItemData::make([
    'name'         => 'Widget Pro',
    'reference'    => 'WGT-001',
    'description'  => 'High-quality widget for professional use',
    'price'        => 2999,             // €29.99
    'tax'          => ItemTax::NORMAL,
    'type'         => ItemType::Product,
    'barcode'      => '5601234567890',
    'category'     => CategoryData::make(['id' => 3]),
    'unit'         => Unit::UNIT,
    'controlStock' => true,
    'enabled'      => true,
]);

$created = Item::create($item)->execute()->getItem();
```

## Available fields

| Field          | Type            | Default             | Description                                     |
| -------------- | --------------- | ------------------- | ----------------------------------------------- |
| `name`         | `?string`       | `null`              | Product name (sent as `title` to provider)      |
| `reference`    | `mixed`         | `null`              | SKU / product code                              |
| `description`  | `?string`       | `null`              | Longer description                              |
| `price`        | `?int`          | `null`              | Gross price in cents (e.g. `2999` = €29.99)     |
| `tax`          | `?ItemTax`      | `null`              | VAT rate                                        |
| `taxExemptionReason` | `?TaxExemptionReason` | `null`  | Required when `tax` is `ItemTax::EXEMPT`        |
| `taxExemptionLaw`    | `?string`       | `null`  | Legal reference for the exemption               |
| `type`         | `?ItemType`     | `ItemType::Product` | Product or service classification               |
| `barcode`      | `?string`       | `null`              | EAN/barcode                                     |
| `category`     | `?CategoryData` | `null`              | Product category (must have provider `id`)      |
| `unit`         | `?ShouldBeUnit` | `null`              | Unit of measure — use `Unit` enum               |
| `controlStock` | `bool`          | `true`              | Track stock for this item                       |
| `enabled`      | `bool`          | `true`              | Whether the item is active in the provider      |

## Units

`ItemData->unit` accepts any enum that implements the `ShouldBeUnit` contract. The package ships with `Unit` as a default implementation:

```php
use CsarCrr\InvoicingIntegration\Enums\Unit;

// Unit::KG  ('kg')
// Unit::UNIT ('unit')
```

You can also define your own enum for units not covered by the default:

```php
use CsarCrr\InvoicingIntegration\Contracts\ShouldBeUnit;

enum MyUnit: string implements ShouldBeUnit
{
    case LITRE = 'litre';
    case HOUR  = 'hour';
}
```

Every unit's string `value` must be mapped to a provider ID in your config. The resolver uses this mapping to translate the enum value into the ID the provider expects:

```php
// config/invoicing-integration.php
'units' => [
    'unit'  => env('CEGID_VENDUS_UNIT_UNIT_ID'),
    'kg'    => env('CEGID_VENDUS_UNIT_KG_ID'),
    'litre' => env('CEGID_VENDUS_UNIT_LITRE_ID'),
    'hour'  => env('CEGID_VENDUS_UNIT_HOUR_ID'),
],
```

If the unit value has no matching key in the config, a `CouldNotGetUnitIdException` is thrown.

## Categories

Pass a `CategoryData` with the provider-assigned category `id`:

```php
use CsarCrr\InvoicingIntegration\Data\CategoryData;

$item = ItemData::make([
    'name'     => 'Widget',
    'category' => CategoryData::make(['id' => 3]),
]);
```

## Accessing the result

After `execute()`, call `getItem()` to retrieve the populated DTO including the provider-assigned `id`:

```php
$created = Item::create($item)->execute()->getItem();

$created->id;        // provider-assigned ID
$created->name;      // item name
$created->reference; // SKU
```

### Additional response data

The provider response may contain fields that the package does not explicitly map onto `ItemData`. Those fields are stored as additional data on the DTO and can be accessed via `getAdditionalData()`:

```php
$created = Item::create($item)->execute()->getItem();

$created->getAdditionalData(); // fields from the provider response not handled by the package
```

## Error handling

```php
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\CouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;

try {
    $created = Item::create($item)->execute()->getItem();
} catch (CouldNotGetUnitIdException $e) {
    // Unit not mapped in config
    Log::error('Unit configuration missing', ['message' => $e->getMessage()]);
} catch (RequestFailedException $e) {
    // Provider rejected the request
    Log::error('Item creation failed', ['message' => $e->getMessage()]);
}
```

For a full list of exceptions, see [Handling Errors](../handling-errors.md).
