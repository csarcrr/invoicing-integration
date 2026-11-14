# Finding Warehouses

Use `Warehouse::find()` to list the warehouses (Moloni) or stores (Cegid Vendus) stored in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

$results = Warehouse::find()->execute();

foreach ($results->getList() as $warehouse) {
    $warehouse->id;
    $warehouse->name;
    $warehouse->getAdditionalData(); // the other fields the provider returns
}
```

- `getList()` returns a `Collection` of `WarehouseData` instances.
- `Warehouse::find()` takes no filters and has no pagination: the provider returns every warehouse in one response.
- The extra fields the provider returns are kept as additional data, read with `getAdditionalData()`.

## Cegid Vendus

The request is sent to `GET stores`. On Cegid Vendus, a store is a record tied to the account's subscription, and each store record has a `type`: `store` or `warehouse`. A Cegid Vendus warehouse is a store record whose type is `warehouse`. The package calls both a warehouse. The endpoint's `type` filter (`all`, `store`, `warehouse`) is not exposed, so the provider default applies; read each row's `type` from `getAdditionalData()` to tell stores from warehouses. Each row is mapped as:

| Cegid Vendus field | `WarehouseData` |
| ------------------ | --------------- |
| `id`               | `id`            |
| `title`            | `name`          |

Every other field, such as `type`, `address` or `status`, is available through `getAdditionalData()`. When Cegid Vendus reports no data, `getList()` is empty.

## Moloni

The request is sent to `warehouses/getAll`. Each row is mapped as:

| Moloni field   | `WarehouseData` |
| -------------- | --------------- |
| `warehouse_id` | `id`            |
| `title`        | `name`          |

Every other field, such as `code`, `is_default` or `address`, is available through `getAdditionalData()`.

## Reference

See also:

- [Warehouses Overview](README.md)
- [API Reference – ShouldFindWarehouse](../api-reference.md#shouldfindwarehouse-contract)
