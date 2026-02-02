<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\Data\ItemData;
use Illuminate\Support\Collection;

trait HasItems
{
    /**
     * @var Collection<int, ItemData>
     */
    protected Collection $items;

    public function item(ItemData $items): self
    {
        $this->items->push($items);

        return $this;
    }

    /**
     * @return Collection<int, ItemData>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }
}
