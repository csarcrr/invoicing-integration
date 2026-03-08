<?php

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ItemData;

interface ShouldCreateItem extends ShouldExecute, ShouldHavePayload
{
    public function getItem(): ItemData;
}
