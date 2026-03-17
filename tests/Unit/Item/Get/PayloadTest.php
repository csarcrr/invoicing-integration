<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('sends the item id in the request url', function (Provider $provider) {
    Http::fake(mockResponse([], 200));

    Item::get(ItemData::make(['id' => 999999]))->execute();

    Http::assertSent(function (Request $request) {
        return Str::contains($request->url(), 999999);
    });
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    $item = ItemData::make([]);

    Item::get($item)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Item ID is required.');
