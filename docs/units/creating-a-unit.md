# Creating a Unit

Use `Unit::create()` to create a unit in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Facades\Unit;

$unit = Unit::create(UnitData::make(['name' => 'Kilograma', 'shortName' => 'Kg']))->execute()->get();

$unit->id;
$unit->name;
$unit->shortName; // null on Vendus
```

- `get()` returns the `UnitData` with the provider-assigned `id`.
- When the provider does not create the unit, `execute()` throws `RequestFailedException` with the provider's error message.

## Vendus

The request is sent to `POST products/units`. `name` is sent as `title`. `shortName` is not supported by Vendus and is ignored. `decimal` and `default` are not sent.

The returned unit is mapped from the response: `id` from `id` and `name` from `title`. `shortName` is always `null`.

## Moloni

The request is sent to `measurementUnits/insert`. `name` is sent as `name` and `shortName` as `short_name`. Moloni requires `short_name`, so set `shortName`: when it is not set, the field is not sent and Moloni's error is returned.

The returned unit is mapped from the response: `id` from `unit_id`. `name` and `shortName` are kept from the input. A response whose `valid` is not `1` throws `RequestFailedException` with the provider's `message`.

## Reference

See also:

- [Units Overview](README.md)
- [Finding Units](finding-units.md)
- [API Reference – ShouldCreateUnit](../api-reference.md#shouldcreateunit-contract)
