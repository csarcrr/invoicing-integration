<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\ItemStock;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldMoveItemStock;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function collect;

class Move implements ShouldMoveItemStock
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected StockMovementData $movement)
    {
        $this->payload = collect();
    }

    public function execute(): self
    {
        $response = Http::provider()->post('products/stocks', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        return $this;
    }

    public function getMovement(): StockMovementData
    {
        return $this->movement;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildItemId();
        $this->buildQuantity();
        $this->buildOptional('store_id', $this->movement->warehouseId);
        $this->buildObservations();

        return $this->payload;
    }

    protected function buildItemId(): void
    {
        throw_if(is_null($this->movement->itemId), InvalidArgumentException::class, 'Item ID is required.');

        $this->payload->put('product_id', $this->movement->itemId);
    }

    protected function buildQuantity(): void
    {
        throw_if(is_null($this->movement->quantity), InvalidArgumentException::class, 'Quantity is required.');

        $this->payload->put('type', $this->movement->quantity > 0 ? 'add' : 'remove');
        $this->payload->put('amount', abs($this->movement->quantity));
    }

    protected function buildObservations(): void
    {
        $observations = collect([$this->movement->date?->toDateTimeString(), $this->movement->notes])
            ->filter()
            ->implode(' - ');

        if ($observations === '') {
            return;
        }

        $this->payload->put('obs', $observations);
    }

    protected function buildOptional(string $key, mixed $value): void
    {
        if (is_null($value)) {
            return;
        }

        $this->payload->put($key, $value);
    }
}
