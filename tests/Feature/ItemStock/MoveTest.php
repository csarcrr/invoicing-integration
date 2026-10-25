<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('moves stock', function (Provider $provider) {
    [$endpoint, $movementId, $sentBody] = match ($provider) {
        Provider::CEGID_VENDUS => ['products/stocks', null, ['"type":"remove"', '"amount":5']],
        Provider::MOLONI => ['productStocks/insert', 777, ['qty=-5']],
    };

    Http::fake([
        "*{$endpoint}*" => mockResponse(fixtures()->response()->itemStock()->files('move')),
    ]);

    $result = ItemStock::move(StockMovementData::make(['itemId' => 1542, 'quantity' => -5]))->execute();

    expect($result->getMovement()->id)->toBe($movementId);

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), $endpoint)
        && Str::containsAll($request->body(), $sentBody));
    Http::assertSentCount(1);
})->with('providers');
