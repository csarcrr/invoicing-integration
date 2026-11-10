# Units

A unit is an account-level record stored in the provider (for example "Kg" or "Unidade"). Each one has a provider-assigned ID, which is the ID the config maps per `Unit` enum case.

> [!NOTE]
> Two things share the name:
>
> - the `Unit` **facade** and `UnitData` list the unit records stored in the provider (this section);
> - the `Unit` **enum** is the fixed list of units an item uses (`KG`, `UNIT`).
>
> When a file needs both the facade and the enum, import one with an alias.

## Quick example

```php
use CsarCrr\InvoicingIntegration\Facades\Unit;

foreach (Unit::find()->execute()->getList() as $unit) {
    echo $unit->id.' '.$unit->name;
}
```

## Available operations

| Operation  | Docs                          |
| ---------- | ----------------------------- |
| Find units | [Finding Units](finding-units.md) |

## Provider support

| Operation  | Cegid Vendus | Moloni | Invoice Express |
| ---------- | ------------ | ------ | --------------- |
| Find Units | ✅           | ✅     | ❌              |
