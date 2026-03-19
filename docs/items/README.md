# Items

The item management API lets you create and manage products or services in your provider's catalog, independently of issuing invoices.

> [!NOTE]
> Item management is separate from invoice line items. When you include an `ItemData` inside `InvoiceData`, you're describing a line on an invoice. When you use `Item::create()`, you're registering a product in the provider's catalog.

## When to use item management

- Pre-registering products in the provider so invoices can reference them by ID
- Syncing your product catalog with your invoicing provider

## Quick example

```php
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Unit;
use CsarCrr\InvoicingIntegration\Facades\Item;

$item = ItemData::make([
    'name'         => 'Widget Pro',
    'reference'    => 'WGT-001',
    'price'        => 2999,
    'tax'          => ItemTax::NORMAL,
    'type'         => ItemType::Product,
    'unit'         => Unit::UNIT,
    'controlStock' => true,
    'enabled'      => true,
]);

$created = Item::create($item)->execute()->getItem();

echo $created->id; // provider-assigned ID
```

## Available operations

| Operation      | Docs                                        |
| -------------- | ------------------------------------------- |
| Create an item | [Creating an Item](creating-an-item.md)     |
| Get an item    | [Getting an Item](getting-an-item.md)       |
| Find items     | [Finding Items](finding-an-item.md)         |

## Provider support

| Operation   | Cegid Vendus | Moloni | Invoice Express |
| ----------- | ------------ | ------ | --------------- |
| Create Item | ✅           | ❌     | ❌              |
| Get Item    | ✅           | ❌     | ❌              |
| Find Items  | ✅           | ❌     | ❌              |
