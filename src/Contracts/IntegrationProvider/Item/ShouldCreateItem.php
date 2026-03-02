<?php

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item;

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use Illuminate\Support\Collection;

interface ShouldCreateItem
{
    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;
}
