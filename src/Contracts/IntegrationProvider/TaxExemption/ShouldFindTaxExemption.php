<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\TaxExemption;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\TaxExemptionData;
use Illuminate\Support\Collection;

interface ShouldFindTaxExemption extends ShouldExecute, ShouldHavePayload
{
    /**
     * @return Collection<int, TaxExemptionData>
     */
    public function get(): Collection;

    public function getUrl(): string;
}
