<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('deletes a stock movement', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support deleting stock movements.');
    }

    Http::fake([
        '*productStocks/delete*' => mockResponse(fixtures()->response()->itemStock()->files('delete')),
    ]);

    ItemStock::delete(StockMovementData::make(['id' => 777]))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'productStocks/delete')
        && Str::contains($request->body(), 'product_stock_id=777'));
    Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'products/getOne'));
})->with('providers');
