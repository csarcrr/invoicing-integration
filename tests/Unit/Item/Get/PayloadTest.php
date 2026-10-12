<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('sends the item id in the request', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->item()->files('get')));

    Item::get(ItemData::make(['id' => 999999]))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), '999999'),
        Provider::MOLONI => Str::contains($request->body(), 'product_id=999999'),
    });
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    $item = ItemData::make([]);

    Item::get($item)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Item ID is required.');
