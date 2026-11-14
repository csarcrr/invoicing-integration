# Warehouses

A warehouse is a stock location stored in the provider. Each one has a provider-assigned ID, which you use wherever the provider asks for a warehouse or store ID.

- **Moloni:** a warehouse (`warehouses/getAll`).
- **Cegid Vendus:** a store (`stores`). On Cegid Vendus, a store is a record tied to the account's subscription, and each store record has a `type`: `store` or `warehouse`. A Cegid Vendus warehouse is a store record whose type is `warehouse`. The package calls both a warehouse. A store is not a Cegid Vendus register (a point-of-sale till).

## Quick example

```php
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

foreach (Warehouse::find()->execute()->getList() as $warehouse) {
    echo $warehouse->id.' '.$warehouse->name;
}
```

## Available operations

| Operation        | Docs                                            |
| ---------------- | ----------------------------------------------- |
| Create warehouse | [Creating a Warehouse](creating-a-warehouse.md) |
| Find warehouses  | [Finding Warehouses](finding-warehouses.md)     |

## Provider support

| Operation        | Cegid Vendus | Moloni | Invoice Express |
| ---------------- | ------------ | ------ | --------------- |
| Create Warehouse | ❌           | ✅     | ❌              |
| Find Warehouses  | ✅           | ✅     | ❌              |
