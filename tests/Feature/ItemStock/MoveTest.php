<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('moves stock', function (Provider $provider) {
    $move = fn () => ItemStock::move(StockMovementData::make(['itemId' => 1542, 'quantity' => -5]));

    if ($provider === Provider::CEGID_VENDUS) {
        expect($move)->toThrow(OperationNotSupportedException::class);

        return;
    }

    Http::fake([
        '*productStocks/insert*' => mockResponse(fixtures()->response()->itemStock()->files('move')),
    ]);

    $result = $move()->execute();

    expect($result->getMovement()->id)->toBe(777);

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'productStocks/insert')
        && Str::contains($request->body(), 'qty=-5'));
    Http::assertSentCount(1);
})->with('providers');
