<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('updates a stock movement and returns the updated product stock', function (Provider $provider) {
    $update = fn () => ProductStock::update(StockMovementData::make(['id' => 777, 'productId' => 1542, 'quantity' => 10]));

    if ($provider === Provider::CEGID_VENDUS) {
        expect($update)->toThrow(OperationNotSupportedException::class);

        return;
    }

    Http::fake([
        '*productStocks/update*' => mockResponse(fixtures()->response()->productStock()->files('update')),
        '*products/getOne*' => mockResponse(fixtures()->response()->item()->files('get')),
    ]);

    $result = $update()->execute();

    expect($result->getMovement()->id)->toBe(777)
        ->and($result->getProductStock()->productId)->toBe(1542)
        ->and($result->getProductStock()->quantity)->toBe(42);

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'productStocks/update')
        && Str::contains($request->body(), 'product_stock_id=777'));
    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'products/getOne'));
})->with('providers');
