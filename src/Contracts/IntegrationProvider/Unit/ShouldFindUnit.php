<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Data\UnitData;
use Illuminate\Support\Collection;

interface ShouldFindUnit extends ShouldExecute
{
    /**
     * @return Collection<int, UnitData>
     */
    public function getList(): Collection;
}
