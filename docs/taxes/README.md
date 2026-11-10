# Taxes

A tax is an account-level record stored in the provider (for example "IVA Normal" at 23%). On Moloni each item tax is sent with the ID of one of these records, which the config maps per `ItemTax` case.

> [!NOTE]
> `TaxData` and the `Tax` facade manage the tax records stored in the provider. The `ItemTax` enum is the fixed list of tax kinds an item uses (`NORMAL`, `EXEMPT`, ...).

## Quick example

```php
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Facades\Tax;

$exempt = Tax::find()->execute()->getList()
    ->first(fn ($tax) => $tax->type === ItemTax::EXEMPT);
```

## Available operations

| Operation    | Docs                              |
| ------------ | --------------------------------- |
| Create a tax | [Creating a Tax](creating-a-tax.md) |
| Find taxes   | [Finding Taxes](finding-taxes.md)   |

## Provider support

| Operation   | Cegid Vendus | Moloni | Invoice Express |
| ----------- | ------------ | ------ | --------------- |
| Create Tax  | ❌           | ✅     | ❌              |
| Find Taxes  | ❌           | ✅     | ❌              |

On Cegid Vendus both operations throw `OperationNotSupportedException`: Vendus takes the tax code (`NOR`, `ISE`, ...), not a tax record ID.
