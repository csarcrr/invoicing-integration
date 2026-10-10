<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    config()->set('cache.default', 'array');
    mockConfiguration(Provider::MOLONI);
    Cache::flush();
});

it('posts the product id to the products get one endpoint', function () {
    Http::fake(mockResponse(fixtures()->response()->item()->files('get')));

    Item::get(ItemData::make(['id' => 123]))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'products/getOne')
        && Str::contains($request->body(), 'product_id=123')
        && Str::contains($request->body(), 'company_id=123456'));
});

it('maps the moloni product into the item', function () {
    Http::fake(mockResponse(fixtures()->response()->item()->files('get')));

    $item = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($item->id)->toBe(123)
        ->and($item->name)->toBe('Wireless Bluetooth Headphones')
        ->and($item->description)->toBe('A small product description used for testing purposes.')
        ->and($item->reference)->toBe('WBH-2026-BLK')
        ->and($item->barcode)->toBe('5601234567890')
        ->and($item->category->id)->toBe(12)
        ->and($item->controlStock)->toBeTrue()
        ->and($item->type)->toBe(ItemType::Product);
});

it('converts the net price into the gross price with the product tax', function () {
    Http::fake(mockResponse(fixtures()->response()->item()->files('get')));

    $item = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($item->price)->toBe(2460)
        ->and($item->tax)->toBe(ItemTax::NORMAL);
});

it('leaves tax and price empty when the product has no taxes', function () {
    $response = fixtures()->response()->item()->files('get');
    $response['taxes'] = [];

    Http::fake(mockResponse($response));

    $item = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($item->tax)->toBeNull()
        ->and($item->price)->toBeNull();
});

it('maps the moloni type to the item type', function (int $moloniType, ItemType $expected) {
    $response = fixtures()->response()->item()->files('get');
    $response['type'] = $moloniType;

    Http::fake(mockResponse($response));

    $item = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($item->type)->toBe($expected);
})->with([
    [1, ItemType::Product],
    [2, ItemType::Service],
    [3, ItemType::Other],
]);

it('keeps unmapped properties in the additional data', function () {
    Http::fake(mockResponse(fixtures()->response()->item()->files('get')));

    $item = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($item->getAdditionalData())
        ->toHaveKeys(['image', 'stock', 'unit_id', 'category'])
        ->not->toHaveKeys(['name', 'summary', 'reference', 'ean', 'price', 'taxes']);
});

it('fails when the item has no id', function () {
    Item::get(ItemData::make(['name' => 'Item Title']))->execute();
})->throws(InvalidArgumentException::class);
