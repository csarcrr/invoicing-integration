<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('gets the stock of a product', function (Provider $provider) {
    $get = fn () => ProductStock::get(ItemData::make(['id' => 1542]));

    if ($provider === Provider::CEGID_VENDUS) {
        expect($get)->toThrow(OperationNotSupportedException::class);

        return;
    }

    Http::fake([
        '*products/getOne*' => mockResponse(fixtures()->response()->item()->files('get')),
    ]);

    $stock = $get()->execute()->getProductStock();

    expect($stock->productId)->toBe(1542)
        ->and($stock->quantity)->toBe(42);

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'products/getOne')
        && Str::contains($request->body(), 'product_id=1542'));
})->with('providers');
