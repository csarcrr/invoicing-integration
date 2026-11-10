# Finding Units

Use `Unit::find()` to list the units stored in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Facades\Unit;

$results = Unit::find()->execute();

foreach ($results->getList() as $unit) {
    $unit->id;
    $unit->name;
    $unit->shortName; // null on Cegid Vendus
}
```

- `getList()` returns a `Collection` of `UnitData` instances.
- `Unit::find()` takes no filters and has no pagination: the provider returns every unit in one response.

## Cegid Vendus

The request is sent to `GET products/units`. Each row is mapped as:

| Cegid Vendus field | `UnitData` |
| ------------------ | ---------- |
| `id`               | `id`       |
| `title`            | `name`     |

`shortName` is always `null`. When Cegid Vendus reports no data, `getList()` is empty.

## Moloni

The request is sent to `measurementUnits/getAll`. Each row is mapped as:

| Moloni field | `UnitData`  |
| ------------ | ----------- |
| `unit_id`    | `id`        |
| `name`       | `name`      |
| `short_name` | `shortName` |

## Reference

See also:

- [Units Overview](README.md)
- [API Reference – ShouldFindUnit](../api-reference.md#shouldfindunit-contract)
