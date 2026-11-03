<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('returns the updated item', function (Provider $provider) {
    fakeItemUpdate($provider);

    $data = Item::update(ItemData::make(['id' => 1542, 'name' => 'New Title']))->execute()->getItem();

    expect($data->id)->toBe(1542)
        ->and($data->name)->toBe('New Title')
        ->and($data->reference)->toBe('reference-1')
        ->and($data->barcode)->toBe('barcode-1')
        ->and($data->price)->toBe(2460)
        ->and($data->tax)->toBe(ItemTax::NORMAL)
        ->and($data->category->id)->toBe(1)
        ->and($data->controlStock)->toBeTrue()
        ->and($data->enabled)->toBeTrue()
        ->and($data->getAdditionalData())->not->toBeEmpty();
})->with('providers');

test('keeps the moloni gross price when only the tax changes', function () {
    mockConfiguration(Provider::MOLONI);
    fakeItemUpdate(Provider::MOLONI);

    $data = Item::update(ItemData::make(['id' => 1542, 'tax' => ItemTax::REDUCED]))->execute()->getItem();

    expect($data->tax)->toBe(ItemTax::REDUCED)
        ->and($data->price)->toBe(2460);
});

test('returns the moloni enabled state that was set', function () {
    mockConfiguration(Provider::MOLONI);
    fakeItemUpdate(Provider::MOLONI);

    $data = Item::update(ItemData::make(['id' => 1542, 'enabled' => false]))->execute()->getItem();

    expect($data->enabled)->toBeFalse();
});

test('clears the moloni exemption reason when the tax is no longer exempt', function () {
    mockConfiguration(Provider::MOLONI);

    $current = fixtures()->response()->item()->files('get');
    $current = [...$current, 'exemption_reason' => 'M01', 'taxes' => [[...$current['taxes'][0], 'tax_id' => 5]]];

    Http::fakeSequence()
        ->pushResponse(mockResponse($current))
        ->pushResponse(mockResponse(fixtures()->response()->item()->files('update')));

    $data = Item::update(ItemData::make(['id' => 1542, 'tax' => ItemTax::NORMAL]))->execute()->getItem();

    expect($data->tax)->toBe(ItemTax::NORMAL)
        ->and($data->taxExemptionReason)->toBeNull();
});

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
                && Str::contains($request->body(), 'product_id=1542')
                && Str::contains($request->body(), 'with_invisible=1'),
            fn (Request $request) => Str::contains($request->url(), 'products/update')
                && Str::contains($request->body(), 'product_id=1542')
                && Str::contains($request->body(), 'name=New+Title')
                && Str::contains($request->body(), 'company_id=123456'),
        ],
    });
})->with('providers');
