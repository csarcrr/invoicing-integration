<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\WarehouseData;

interface ShouldCreateWarehouse extends ShouldExecute, ShouldHavePayload
{
    public function get(): WarehouseData;

    public function getUrl(): string;
}
