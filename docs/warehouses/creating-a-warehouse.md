# Creating a Warehouse

Use `Warehouse::create()` to create a warehouse in the provider. Supported on Moloni; Cegid Vendus throws `OperationNotSupportedException`.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

$warehouse = Warehouse::create(WarehouseData::make([
    'name' => 'Armazém Principal',
    'code' => 'AP',
    'address' => 'Rua Nova de Mil Fontes, 35',
    'city' => 'Setúbal',
    'postalCode' => '1100-234',
    'country' => 'PT',
    'isDefault' => true,
]))->execute()->getWarehouse();

echo $warehouse->id; // provider-assigned ID
```

- `getWarehouse()` returns the `WarehouseData` with the provider-assigned `id`.
- When the provider does not create the warehouse, `execute()` throws `RequestFailedException` with the provider's error message.

## Moloni

The request is sent to `warehouses/insert`. The fields are mapped as follows:

| `WarehouseData` | Moloni field |
| --------------- | ------------ |
| `name`          | `title`      |
| `code`          | `code`       |
| `address`       | `address`    |
| `city`          | `city`       |
| `postalCode`    | `zip_code`   |
| `country`       | `country_id` |
| `isDefault`     | `is_default` |

- `country` is an ISO 3166-1 alpha-2 code. The package looks up the Moloni `country_id` with `countries/getAll` before the warehouse is sent, so one extra request is made when `country` is set.
- `isDefault` is sent as `1` when `true` and `0` when `false`. It is left out when not set.
- Fields that are not set are left out of the request.

The returned warehouse is mapped from the response: `id` from `warehouse_id`. The other fields are kept from the input. A response whose `valid` is not `1` throws `RequestFailedException` with the provider's `message`.

> [!NOTE]
> Moloni moves all stock to the first warehouse created. When `isDefault` is `true`, the previous default warehouse is unmarked.

## Cegid Vendus

Not supported yet: `Warehouse::create()` throws `OperationNotSupportedException`. On Cegid Vendus, a store is a record tied to the account's subscription, and each store record has a `type`: `store` or `warehouse`. A Cegid Vendus warehouse is a store record whose type is `warehouse`. The package calls both a warehouse. Creating a store (`POST stores`) exists only in Cegid Vendus API v1.2, and the package calls v1.1. Support follows when the package moves to v1.2.

## Reference

See also:

- [Warehouses Overview](README.md)
- [Finding Warehouses](finding-warehouses.md)
- [API Reference – ShouldCreateWarehouse](../api-reference.md#shouldcreatewarehouse-contract)
