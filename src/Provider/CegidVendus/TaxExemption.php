<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

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
            'code' => $exemption['id'],
            'description' => $exemption['text'],
        ])->additional(collect($exemption)->except(['id', 'text'])->toArray());
    }
}
