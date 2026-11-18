<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use Illuminate\Support\Collection;

interface ShouldFindWarehouse extends ShouldExecute, ShouldHavePayload
{
    /**
     * @return Collection<int, WarehouseData>
     */
    public function get(): Collection;

    public function getUrl(): string;
}
