<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('deletes a stock movement', function (Provider $provider) {
    $delete = fn () => ProductStock::delete(StockMovementData::make(['id' => 777]));

    if ($provider === Provider::CEGID_VENDUS) {
        expect($delete)->toThrow(OperationNotSupportedException::class);

        return;
    }

    Http::fake([
        '*productStocks/delete*' => mockResponse(fixtures()->response()->productStock()->files('delete')),
    ]);

    $delete()->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'productStocks/delete')
        && Str::contains($request->body(), 'product_stock_id=777'));
    Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'products/getOne'));
})->with('providers');
