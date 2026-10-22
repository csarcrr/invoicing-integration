<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Data\ItemStockData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

abstract class ItemStock
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    protected function fetchItemStock(int $itemId): ItemStockData
    {
        $response = Http::provider()->post('products/getOne', ['product_id' => $itemId]);

        Http::handleUnwantedFailures($response);

        return ItemStockData::make([
            'itemId' => $itemId,
            'quantity' => $response->json('stock'),
        ]);
    }

    protected function buildItemId(?int $itemId): void
    {
        throw_if(is_null($itemId), InvalidArgumentException::class, 'Item ID is required.');

        $this->payload->put('product_id', $itemId);
    }
}
