<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\ProductStock;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldGetProductStock;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\ProductStockData;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ProductStock;
use Illuminate\Support\Collection;

use function collect;

class Get extends ProductStock implements ShouldGetProductStock
{
    protected ProductStockData $productStock;

    public function __construct(protected ItemData $item)
    {
        $this->payload = collect();
    }

    public function execute(): self
    {
        $this->getPayload();

        $this->productStock = $this->fetchProductStock($this->item->id);

        return $this;
    }

    public function getProductStock(): ProductStockData
    {
        return $this->productStock;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildProductId($this->item->id);

        return $this->payload;
    }
}
