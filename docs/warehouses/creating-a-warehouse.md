# Creating a Warehouse

Use `Warehouse::create()` to create a warehouse in the provider. Supported on Moloni and Cegid Vendus.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

$warehouse = Warehouse::create(WarehouseData::make([
    'name' => 'Armazém Principal',
    'address' => 'Rua Nova de Mil Fontes, 35',
    'city' => 'Setúbal',
    'postalCode' => '1100-234',
    'country' => 'PT',
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
| `address`       | `address`    |
| `city`          | `city`       |
| `postalCode`    | `zip_code`   |
| `country`       | `country_id` |

- Moloni requires a `code`. The package generates a random 8-character code and sends it with the request; it is not an input. The generated code is returned in `getAdditionalData()['code']`.
- `country` is an ISO 3166-1 alpha-2 code. The package looks up the Moloni `country_id` with `countries/getAll` before the warehouse is sent, so one extra request is made when `country` is set.
- Fields that are not set are left out of the request.

The returned warehouse is mapped from the response: `id` from `warehouse_id`. The other fields are kept from the input, and the generated `code` is kept in additional data. A response whose `valid` is not `1` throws `RequestFailedException` with the provider's `message`.

> [!NOTE]
> Moloni moves all stock to the first warehouse created.

## Cegid Vendus

The request is sent to `stores` with `POST`. The fields are mapped as follows:

| `WarehouseData` | Cegid Vendus field |
| --------------- | ------------------ |
| `name`          | `title`            |
| `address`       | `address`          |
| `city`          | `local`            |
| `postalCode`    | `postalcode`       |
| `country`       | not sent           |

- `country` is not sent, because the Cegid Vendus store request has no country field.
- The package does not generate a `code`.
- Fields that are not set are left out of the request.

The returned warehouse is mapped from the response: `id` from the response `id`. The other fields are kept from the input. The remaining response fields, such as `type`, `city`, `country` and `status`, are in `getAdditionalData()`.

On Cegid Vendus, a store is a record tied to the account's subscription, and each store record has a `type`: `store` or `warehouse`. A Cegid Vendus warehouse is a store record whose type is `warehouse`. The package calls both a warehouse.

> [!NOTE]
> The provider decides whether the account can have another store.

## Reference

See also:

- [Warehouses Overview](README.md)
- [Finding Warehouses](finding-warehouses.md)
- [API Reference – ShouldCreateWarehouse](../api-reference.md#shouldcreatewarehouse-contract)
