# Working with Tax Exemptions

Many Portuguese document types require you to explicitly state why VAT is exempt. This guide shows
how to configure exemptions when building your `InvoiceData` payload. An exemption reason is the provider's code, such as `M04`. The codes stored in your provider are listed by [`TaxExemption::find()`](../taxes/finding-tax-exemptions.md).

## When to Use an Exemption

> [!WARNING]
> Tax law interpretation can be complex and varies by business context. Always consult your certified accountant to determine which exemption code applies to your specific situation and ensure compliance with Portuguese tax regulations.

## Step-by-Step

```php
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\TaxExemptionData;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Facades\TaxExemption;

$exemptions = TaxExemption::find()->execute()->getList();

/** @var TaxExemptionData $exemption */
$exemption = $exemptions->firstWhere('code', 'M04');

$invoiceData = InvoiceData::make([
    'items' => [
        ItemData::make([
            'reference' => 'CONSULTING',
            'price' => 5000,
            'tax' => ItemTax::EXEMPT,
            'taxExemptionReason' => $exemption->code,
            'taxExemptionLaw' => $exemption->getAdditionalData()['law'] ?? null,
        ]),
    ],
]);
```

1. Get the exemption reasons stored in your provider with [`TaxExemption::find()`](../taxes/finding-tax-exemptions.md). Each one is a `TaxExemptionData` with a `code` and a `description`.
2. Pick the `TaxExemptionData` that applies. Show `description` to the person who chooses, or select by `code`.
3. Set the item tax rate to `ItemTax::EXEMPT`.
4. Set `taxExemptionReason` to the `code` of that `TaxExemptionData`. The package sends the code as given and does not check it.
5. Optionally set `taxExemptionLaw` to the legal article (recommended for audit trails and AT requirements). Cegid Vendus returns the law text of each exemption in `getAdditionalData()['law']`. Moloni returns none, so write the text yourself.

`TaxExemption::find()` sends one request to the provider each time it is executed. `taxExemptionReason` only needs the code, so an item can also take a code you already know, such as `'M04'`, with no request.

> Setting `taxExemptionReason` throws an `ExemptionCanOnlyBeUsedWithExemptTaxException` if the item tax rate is
> not `EXEMPT`. Likewise, defining `taxExemptionLaw` requires an exemption reason first.

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
- [Finding Tax Exemptions](../taxes/finding-tax-exemptions.md)
- [Handling Errors](../handling-errors.md)
