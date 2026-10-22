<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Data\ProductStockData;

interface ShouldMoveProductStock extends ShouldExecute, ShouldHavePayload
{
    public function getMovement(): StockMovementData;

    public function getProductStock(): ProductStockData;
}
