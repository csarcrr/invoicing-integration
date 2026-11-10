# Finding Taxes

Use `Tax::find()` to list the taxes stored in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Facades\Tax;

$results = Tax::find()->execute();

foreach ($results->getList() as $tax) {
    $tax->id;
    $tax->name;
    $tax->rate;            // float
    $tax->type;            // ItemTax enum case, or null
    $tax->fiscalZone;      // e.g. PT, PT-AC, PT-MA
    $tax->exemptionReason; // TaxExemptionReason enum case, or null
}
```

- `getList()` returns a `Collection` of `TaxData` instances.
- `Tax::find()` takes no filters and has no pagination: the provider returns every tax in one response.

## Moloni

The request is sent to `taxes/getAll`. Each row is mapped as:

| Moloni field       | `TaxData`         |
| ------------------ | ----------------- |
| `tax_id`           | `id`              |
| `name`             | `name`            |
| `value`            | `rate`            |
| `vat_type`         | `type`            |
| `fiscal_zone`      | `fiscalZone`      |
| `exemption_reason` | `exemptionReason` |

`vat_type` maps to `ItemTax`: `NOR` → `NORMAL`, `INT` → `INTERMEDIATE`, `RED` → `REDUCED`, `ISE` → `EXEMPT`, `OUT` → `OTHER`. A tax that is not VAT (for example stamp duty) has no `vat_type` and maps to `null`. An empty `exemption_reason` maps to `null`.

An account can hold several taxes of the same type, one per fiscal zone (`PT`, `PT-AC`, `PT-MA`) and sometimes more than one in the same zone.

## Cegid Vendus

Not supported: `Tax::find()` throws `OperationNotSupportedException`.

## Reference

See also:

- [Taxes Overview](README.md)
- [Creating a Tax](creating-a-tax.md)
- [API Reference – ShouldFindTax](../api-reference.md#shouldfindtax-contract)
