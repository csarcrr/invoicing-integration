# Creating a Tax

Use `Tax::create()` to store a new VAT tax in the provider.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Facades\Tax;

$tax = Tax::create(TaxData::make([
    'name' => 'Isento',
    'rate' => 0,
    'type' => ItemTax::EXEMPT,
    'fiscalZone' => 'PT',
    'exemptionReason' => TaxExemptionReason::M07,
]))->execute()->getTax();

echo $tax->id; // provider-assigned ID
```

## Moloni

The request is sent to `taxes/insert`:

| `TaxData`         | Moloni field       |
| ----------------- | ------------------ |
| `name`            | `name`             |
| `rate`            | `value`            |
| `type`            | `vat_type`         |
| `fiscalZone`      | `fiscal_zone`      |
| `exemptionReason` | `exemption_reason` |

The tax is always created as a VAT percentage that is not active by default: `type=1`, `saft_type=1`, `stamp_tax=''` and `active_by_default=0`. `exemption_reason` is sent empty when not set. `rate`, `type` and `fiscalZone` are left out when not set.

> [!NOTE]
> Moloni requires `value`, `vat_type` and `fiscal_zone`, and requires an `exemptionReason` when the rate is 0. On Portuguese fiscal zones it only accepts `M01` to `M16`. A missing or invalid field throws `RequestFailedException` with the Moloni error.
>
> Moloni treats a `value` of 0 as missing and rejects it (`2 value 0 null null 0 100 - Field 'value' must be float, greater or equal to 0, lesser or equal to 100`), so a 0% exempt tax cannot be created through the API. Create it in Moloni instead.

## Cegid Vendus

Not supported: `Tax::create()` throws `OperationNotSupportedException`.

## Reference

See also:

- [Taxes Overview](README.md)
- [Finding Taxes](finding-taxes.md)
- [API Reference – ShouldCreateTax](../api-reference.md#shouldcreatetax-contract)
