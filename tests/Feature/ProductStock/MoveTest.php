<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('moves stock and returns the updated product stock', function (Provider $provider) {
    $move = fn () => ProductStock::move(StockMovementData::make(['productId' => 1542, 'quantity' => -5]));

    if ($provider === Provider::CEGID_VENDUS) {
        expect($move)->toThrow(OperationNotSupportedException::class);

        return;
    }

    Http::fake([
        '*productStocks/insert*' => mockResponse(fixtures()->response()->productStock()->files('move')),
        '*products/getOne*' => mockResponse(fixtures()->response()->item()->files('get')),
    ]);

    $result = $move()->execute();

    expect($result->getMovement()->id)->toBe(777)
        ->and($result->getProductStock()->productId)->toBe(1542)
        ->and($result->getProductStock()->quantity)->toBe(42);

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'productStocks/insert')
        && Str::contains($request->body(), 'qty=-5'));
    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'products/getOne')
        && Str::contains($request->body(), 'product_id=1542'));
})->with('providers');
