<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Data\TaxExemptionData;
use CsarCrr\InvoicingIntegration\Provider\Base;

use function collect;

/** @extends Base<TaxExemptionData> */
abstract class TaxExemption extends Base
{
    /**
     * @param  array<string, mixed>  $exemption
     */
    protected function toTaxExemptionData(array $exemption): TaxExemptionData
    {
        return TaxExemptionData::make([
            'code' => $exemption['code'],
            'description' => $exemption['description'],
        ])->additional(collect($exemption)->except(['code', 'description'])->toArray());
    }
}
