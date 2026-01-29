# Working with Tax Exemptions

Many Portuguese document types require you to explicitly state why VAT is exempt. This guide shows
how to configure exemptions with the fluent builder and lists every supported
`TaxExemptionReason` code so that you never need to peek at the enum source.

## When to Use an Exemption

> **Important:** Tax law interpretation can be complex and varies by business context. Always consult your certified accountant to determine which exemption code applies to your specific situation and ensure compliance with Portuguese tax regulations.

## Step-by-Step

```php
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\ValueObjects\ItemData;

$item = ItemData::from([
    'reference' => 'CONSULTING',
    'price' => 5000,
    'tax' => ItemTax::EXEMPT,
    'taxExemptionReason' => TaxExemptionReason::M04,
    'taxExemptionLaw' => TaxExemptionReason::M04->laws()[0],
]);

$invoice->item($item);
```

1. Set the item tax rate to `ItemTax::EXEMPT`.
2. Provide the `taxExemptionReason` property with the desired `TaxExemptionReason`.
3. Optionally include the specific legal article via the `taxExemptionLaw` property (recommended for audit
   trails and AT requirements).

> Setting `taxExemptionReason` throws an `ExemptionCanOnlyBeUsedWithExemptTaxException` if the item tax rate is
> not `EXEMPT`. Likewise, defining `taxExemptionLaw` requires an exemption reason first.

## Exemption Codes Cheat Sheet

Each exemption reason provides a complete list of referenced laws. Call
`TaxExemptionReason::M09->laws()` to retrieve them and pick the appropriate index when multiple
articles exist.

| Code | Laws (`laws()[n]`)                                                                                                                                           |
| ---- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| M01  | 0: Art. 16.º, n.º 6, al. a) do CIVA<br>1: Art. 16.º, n.º 6, al. b) do CIVA<br>2: Art. 16.º, n.º 6, al. c) do CIVA<br>3: Art. 16.º, n.º 6, al. d) do CIVA     |
| M02  | 0: Artigo 6.º do Decreto-Lei n.º 198/90, de 19 de junho                                                                                                      |
| M03  | 0: Não utilizar após 2022                                                                                                                                    |
| M04  | 0: Artigo 13.º do CIVA                                                                                                                                       |
| M05  | 0: Artigo 14.º do CIVA                                                                                                                                       |
| M06  | 0: Artigo 15.º do CIVA                                                                                                                                       |
| M07  | 0: Artigo 9.º do CIVA                                                                                                                                        |
| M08  | 0: Utilizar alternativa entre M30 e M43                                                                                                                      |
| M09  | 0: Artigo 60.º do CIVA<br>1: Artigo 72.º n.º 4 do CIVA                                                                                                       |
| M10  | 0: Artigo 53.º n.º 1 do CIVA<br>1: Artigo 57.º do CIVA                                                                                                       |
| M11  | 0: Decreto-Lei n.º 346/85, de 23 de agosto                                                                                                                   |
| M12  | 0: Decreto-Lei n.º 221/85, de 3 de julho                                                                                                                     |
| M13  | 0: Decreto-Lei n.º 199/96, de 18 de outubro                                                                                                                  |
| M14  | 0: Decreto-Lei n.º 199/96, de 18 de outubro                                                                                                                  |
| M15  | 0: Decreto-Lei n.º 199/96, de 18 de outubro                                                                                                                  |
| M16  | 0: Artigo 14.º do RITI                                                                                                                                       |
| M19  | 0: Isenções temporárias em diploma próprio                                                                                                                   |
| M20  | 0: Artigo 59.º-D n.º 2 do CIVA                                                                                                                               |
| M21  | 0: Artigo 72.º n.º 4 do CIVA                                                                                                                                 |
| M25  | 0: Artigo 38.º n.º 1 alínea a)                                                                                                                               |
| M26  | 0: Lei n.º 17/2023                                                                                                                                           |
| M30  | 0: Artigo 2.º n.º 1 alínea i) do CIVA                                                                                                                        |
| M31  | 0: Artigo 2.º n.º 1 alínea j) do CIVA                                                                                                                        |
| M32  | 0: Artigo 2.º n.º 1 alínea l) do CIVA                                                                                                                        |
| M33  | 0: Artigo 2.º n.º 1 alínea m) do CIVA                                                                                                                        |
| M34  | 0: Artigo 2.º n.º 1 alínea n) do CIVA                                                                                                                        |
| M40  | 0: Artigo 6.º n.º 6 alínea a) do CIVA, a contrário                                                                                                           |
| M41  | 0: Artigo 8.º n.º 3 do RITI                                                                                                                                  |
| M42  | 0: Decreto-Lei n.º 21/2007, de 29 de janeiro                                                                                                                 |
| M43  | 0: Decreto-Lei n.º 362/99, de 16 de setembro                                                                                                                 |
| M44  | 0: Artigo 6.º do CIVA                                                                                                                                        |
| M45  | 0: Artigo 58.º-A do CIVA                                                                                                                                     |
| M46  | 0: Decreto-Lei n.º 19/2017, de 14 de fevereiro                                                                                                               |
| M99  | 0: Artigo 2.º, n.º 2 do CIVA<br>1: Artigo 3.º, n.º 4 do CIVA<br>2: Artigo 3.º, n.º 6 do CIVA<br>3: Artigo 3.º, n.º 7 do CIVA<br>4: Artigo 4.º, n.º 5 do CIVA |

> Need the precise law text for the invoice footer? Every enum case exposes a `laws()` helper that
> returns the officially recommended references.

> **Tip:** To show which index you selected, log the array index alongside the law text when persisting invoices. This helps with audits and future migrations.

## Validating Inputs

- Providing `taxExemptionReason` without first setting `tax` to `ItemTax::EXEMPT` raises an
  `ExemptionCanOnlyBeUsedWithExemptTaxException`.
- Providing `taxExemptionLaw` without `taxExemptionReason` raises an
  `ExemptionLawCanOnlyBeUsedWithExemptionException`.
- Credit notes must still reference the original document line via the `relatedDocument` property.

Use these exceptions to surface meaningful error messages in your UI or job logs.

## Related Reading

- [API Reference – Items](../api-reference.md#item)
- [Creating an Invoice](creating-an-invoice.md#tax-configuration)
- [Handling Errors](../handling-errors.md)
