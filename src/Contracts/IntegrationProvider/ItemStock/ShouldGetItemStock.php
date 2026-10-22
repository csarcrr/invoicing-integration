<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ItemStockData;

interface ShouldGetItemStock extends ShouldExecute, ShouldHavePayload
{
    public function getItemStock(): ItemStockData;
}
