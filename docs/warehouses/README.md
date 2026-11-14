# Warehouses

A warehouse is a stock location stored in the provider. On Moloni it is a warehouse (`warehouses/getAll`); on Cegid Vendus it is a store (`stores`), which can be a store or a warehouse. Each one has a provider-assigned ID, which you use wherever the provider asks for a warehouse or store ID.

## Quick example

```php
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

foreach (Warehouse::find()->execute()->getList() as $warehouse) {
    echo $warehouse->id.' '.$warehouse->name;
}
```

## Available operations

| Operation       | Docs                                          |
| --------------- | --------------------------------------------- |
| Find warehouses | [Finding Warehouses](finding-warehouses.md)   |

## Provider support

| Operation       | Cegid Vendus | Moloni | Invoice Express |
| --------------- | ------------ | ------ | --------------- |
| Find Warehouses | ✅           | ✅     | ❌              |
