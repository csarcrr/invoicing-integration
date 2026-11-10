<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Data\TaxData;
use Illuminate\Support\Collection;

interface ShouldFindTax extends ShouldExecute
{
    /**
     * @return Collection<int, TaxData>
     */
    public function getList(): Collection;
}
