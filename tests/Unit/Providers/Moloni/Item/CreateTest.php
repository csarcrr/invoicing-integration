<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetTaxIdException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\MissingCategoryException;
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

it('converts the gross price into the net price with the configured tax', function () {
    $payload = Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
        'price' => 2460,
        'tax' => ItemTax::NORMAL,
    ]))->getPayload();

    expect($payload->get('price'))->toBe(20.0)
        ->and($payload->get('taxes'))->toBe([[
            'tax_id' => 1,
            'value' => 23.0,
            'order' => 0,
            'cumulative' => 0,
        ]])
        ->and($payload->has('exemption_reason'))->toBeFalse();
});

it('sends no taxes for exempt items', function () {
    $payload = Item::create(ItemData::make([
        'category' => CategoryData::make(['id' => 1]),
        'price' => 2000,
        'tax' => ItemTax::EXEMPT,
    ]))->getPayload();

    expect($payload->has('taxes'))->toBeFalse()
        ->and($payload->get('price'))->toBe(20.0);
});

it('maps the item type to the moloni type', function (ItemType $type, int $expected) {
    $payload = Item::create(ItemData::make([
        'category' => CategoryData::make(['id' => 1]),
        'type' => $type,
    ]))->getPayload();

    expect($payload->get('type'))->toBe($expected);
})->with([
    [ItemType::Product, 1],
    [ItemType::Service, 2],
    [ItemType::Other, 3],
]);

it('fails when the tax is not configured', function () {
    config()->set('invoicing-integration.providers.'.Provider::MOLONI->value.'.taxes', []);

    Item::create(ItemData::make([
        'category' => CategoryData::make(['id' => 1]),
        'tax' => ItemTax::NORMAL,
    ]))->getPayload();
})->throws(CouldNotGetTaxIdException::class);

it('fails when no category is given', function () {
    Item::create(ItemData::make(['name' => 'Item Title']))->getPayload();
})->throws(MissingCategoryException::class);

it('sets the item id from the moloni product id', function () {
    Http::fake(mockResponse(fixtures()->response()->item()->files('create')));

    $item = Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
    ]))->execute()->getItem();

    expect($item->id)->toBe(1542);
});

it('posts the payload to the products insert endpoint', function () {
    Http::fake(mockResponse(fixtures()->response()->item()->files('create')));

    Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
    ]))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'products/insert')
        && Str::contains($request->body(), 'category_id=1')
        && Str::contains($request->body(), 'company_id=123456'));
});
