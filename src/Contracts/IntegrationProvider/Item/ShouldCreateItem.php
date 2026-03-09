<?php

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use Spatie\LaravelData\Data;

interface ShouldCreateItem extends ShouldExecute, ShouldHavePayload
{
    /**
     * @return ItemData
     */
    public function getItem(): Data;
}
