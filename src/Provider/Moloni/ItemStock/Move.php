<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\ItemStock;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldMoveItemStock;
use CsarCrr\InvoicingIntegration\Data\ItemStockData;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Provider\Moloni\ItemStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function collect;

class Move extends ItemStock implements ShouldMoveItemStock
{
    protected ItemStockData $itemStock;

    public function __construct(protected StockMovementData $movement)
    {
        $this->payload = collect();
    }

    public function execute(): self
    {
        $response = Http::provider()->post('productStocks/insert', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        $this->movement->id = $response->json('product_stock_id');
        $this->itemStock = $this->fetchItemStock($this->movement->itemId);

        return $this;
    }

    public function getMovement(): StockMovementData
    {
        return $this->movement;
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
        $this->buildItemId($this->movement->itemId);
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
