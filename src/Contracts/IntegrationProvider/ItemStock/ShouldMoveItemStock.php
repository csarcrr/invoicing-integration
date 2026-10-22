<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;

interface ShouldMoveItemStock extends ShouldExecute, ShouldHavePayload
{
    public function getMovement(): StockMovementData;
}
