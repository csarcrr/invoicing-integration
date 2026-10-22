<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Data\ProductStockData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

abstract class ProductStock
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    protected function fetchProductStock(int $productId): ProductStockData
    {
        $response = Http::provider()->post('products/getOne', ['product_id' => $productId]);

        Http::handleUnwantedFailures($response);

        return ProductStockData::make([
            'productId' => $productId,
            'quantity' => $response->json('stock'),
        ]);
    }

    protected function buildProductId(?int $productId): void
    {
        throw_if(is_null($productId), InvalidArgumentException::class, 'Product ID is required.');

        $this->payload->put('product_id', $productId);
    }
}
