# Finding Tax Exemptions

Use `TaxExemption::find()` to list the tax exemption reasons stored in the provider. Use the `code` of an exemption as the `taxExemptionReason` of an item or the `exemptionReason` of a tax.

## Basic usage

```php
use CsarCrr\InvoicingIntegration\Facades\TaxExemption;

$results = TaxExemption::find()->execute();

foreach ($results->getList() as $exemption) {
    $exemption->code;                // e.g. M07
    $exemption->description;         // provider text
    $exemption->getAdditionalData(); // the other fields the provider returns
}
```

- `getList()` returns a `Collection` of `TaxExemptionData` instances.
- `TaxExemption::find()` takes no filters and has no pagination: the provider returns every exemption reason in one response.
- The extra fields the provider returns are kept as additional data, read with `getAdditionalData()`.

## Cegid Vendus

The request is sent to `GET taxes/exemptions`. Each row is mapped as:

| Cegid Vendus field | `TaxExemptionData` |
| ------------------ | ------------------ |
| `id`               | `code`             |
| `text`             | `description`      |

The laws of each exemption are in `getAdditionalData()['law']`, as a list of texts. When Cegid Vendus reports no data, `getList()` is empty.

## Moloni

The request is sent to `taxExemptions/getAll`. Each row is mapped as:

| Moloni field  | `TaxExemptionData` |
| ------------- | ------------------ |
| `code`        | `code`             |
| `description` | `description`      |

Every other field, such as `name` or `visibility`, is available through `getAdditionalData()`. Moloni returns no law text.

## Reference

See also:

- [Taxes Overview](README.md)
- [Tax Exemptions](../invoices/tax-exemption.md)
- [API Reference – ShouldFindTaxExemption](../api-reference.md#shouldfindtaxexemption-contract)
