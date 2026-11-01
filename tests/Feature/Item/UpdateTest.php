<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('updates an item', function (Provider $provider) {
    fakeItemUpdate($provider);

    $data = Item::update(ItemData::make(['id' => 1542, 'name' => 'New Title']))->execute()->getItem();

    expect($data->id)->toBe(1542)
        ->and($data->name)->toBe('New Title')
        ->and($data->getAdditionalData())->not->toBeEmpty();
})->with('providers');

test('sends the update to the provider', function (Provider $provider) {
    fakeItemUpdate($provider);

    Item::update(ItemData::make(['id' => 1542, 'name' => 'New Title']))->execute();

    Http::assertSentInOrder(match ($provider) {
        Provider::CEGID_VENDUS => [
            fn (Request $request) => $request->method() === 'PATCH'
                && Str::endsWith($request->url(), 'products/1542'),
        ],
        Provider::MOLONI => [
            fn (Request $request) => Str::contains($request->url(), 'products/getOne')
                && Str::contains($request->body(), 'product_id=1542'),
            fn (Request $request) => Str::contains($request->url(), 'products/update')
                && Str::contains($request->body(), 'product_id=1542')
                && Str::contains($request->body(), 'name=New+Title')
                && Str::contains($request->body(), 'company_id=123456'),
        ],
    });
})->with('providers');
