<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('moves stock', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->itemStock()->files('move')));

    $result = ItemStock::move(StockMovementData::make(['itemId' => 1542, 'quantity' => -5]))->execute();

    expect($result->getMovement()->id)->toBe(match ($provider) {
        Provider::CEGID_VENDUS => null,
        Provider::MOLONI => 777,
    });

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), 'products/stocks')
            && $request['type'] === 'remove'
            && $request['amount'] === 5,
        Provider::MOLONI => Str::contains($request->url(), 'productStocks/insert')
            && Str::contains($request->body(), 'qty=-5'),
    });
    Http::assertSentCount(1);
})->with('providers');
