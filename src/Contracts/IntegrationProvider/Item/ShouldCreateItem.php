<?php

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item;

use Illuminate\Support\Collection;

interface ShouldCreateItem
{
    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;
}
