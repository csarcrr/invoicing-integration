<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\ItemStock;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldDeleteItemStock;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function collect;

class Delete implements ShouldDeleteItemStock
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected StockMovementData $movement)
    {
        $this->payload = collect();
    }

    public function execute(): self
    {
        $response = Http::provider()->post('productStocks/delete', $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        throw_if(is_null($this->movement->id), InvalidArgumentException::class, 'Movement ID is required.');

        $this->payload->put('product_stock_id', $this->movement->id);

        return $this->payload;
    }
}
