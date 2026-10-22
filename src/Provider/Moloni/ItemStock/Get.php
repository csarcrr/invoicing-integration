<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\ItemStock;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldGetItemStock;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\ItemStockData;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ItemStock;
use Illuminate\Support\Collection;

use function collect;

class Get extends ItemStock implements ShouldGetItemStock
{
    protected ItemStockData $itemStock;

    public function __construct(protected ItemData $item)
    {
        $this->payload = collect();
    }

    public function execute(): self
    {
        $this->getPayload();

        $this->itemStock = $this->fetchItemStock($this->item->id);

        return $this;
    }

    public function getItemStock(): ItemStockData
    {
        return $this->itemStock;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildItemId($this->item->id);

        return $this->payload;
    }
}
