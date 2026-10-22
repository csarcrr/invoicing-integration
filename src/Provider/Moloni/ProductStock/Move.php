<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\ProductStock;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldMoveProductStock;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Data\ProductStockData;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ProductStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function collect;

class Move extends ProductStock implements ShouldMoveProductStock
{
    protected ProductStockData $productStock;

    public function __construct(protected StockMovementData $movement)
    {
        $this->payload = collect();
    }

    public function execute(): self
    {
        $response = Http::provider()->post('productStocks/insert', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->movement->id = $response->json('product_stock_id');
        $this->productStock = $this->fetchProductStock($this->movement->productId);

        return $this;
    }

    public function getMovement(): StockMovementData
    {
        return $this->movement;
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
        $this->buildProductId($this->movement->productId);
        $this->buildQuantity();
        $this->buildDate();
        $this->buildOptional('warehouse_id', $this->movement->warehouseId);
        $this->buildOptional('notes', $this->movement->notes);

        return $this->payload;
    }

    protected function buildQuantity(): void
    {
        throw_if(is_null($this->movement->quantity), InvalidArgumentException::class, 'Quantity is required.');

        $this->payload->put('qty', $this->movement->quantity);
    }

    protected function buildOptional(string $key, mixed $value): void
    {
        if (is_null($value)) {
            return;
        }

        $this->payload->put($key, $value);
    }

    protected function buildDate(): void
    {
        $this->payload->put('movement_date', ($this->movement->date ?? Carbon::now())->toDateTimeString());
    }
}
