<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use Illuminate\Support\Collection;

trait HasItems
{
    /**
     * @var Collection<int, Item>
     */
    protected Collection $items;

    public function item(Item $items): self
    {
        $this->items->push($items);

        return $this;
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }
}
