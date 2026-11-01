<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\ItemStockData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Support\Facades\Http;

test('an item get request is successful', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($responseFixture)));

    $item = ItemData::make(['id' => 123]);

    $data = Item::get($item)->execute()->getItem();

    expect($data->id)->toBe(123)
        ->and($data->name)->toBe('Item Title')
        ->and($data->description)->toBe('A small product description used for testing purposes.')
        ->and($data->reference)->toBe('reference-1')
        ->and($data->barcode)->toBe('barcode-1')
        ->and($data->getAdditionalData())->not->toBeEmpty()
        ->and($data->getAdditionalData())->not->toHaveKeys(['reference', 'barcode']);

    Http::assertSentCount(1);
})->with('providers', ['get']);

test('supported properties are not filled in additional data', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($responseFixture)));

    $item = ItemData::make(['id' => 123]);

    $data = Item::get($item)->execute()->getItem();

    [$unmapped, $mapped] = match ($provider) {
        Provider::CEGID_VENDUS => [
            ['lot_control'],
            ['title', 'description', 'reference', 'barcode', 'gross_price', 'type_id', 'tax_id', 'category_id', 'stock_control'],
        ],
        Provider::MOLONI => [
            ['image', 'stock', 'unit_id', 'category'],
            ['name', 'summary', 'reference', 'ean', 'price', 'taxes', 'type', 'category_id', 'has_stock'],
        ],
    };

    expect($data->getAdditionalData())
        ->toHaveKeys($unmapped)
        ->not->toHaveKeys($mapped);
})->with('providers', ['get']);

test('maps the category and the stock control', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($responseFixture)));

    $data = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($data->category->id)->toBe(1)
        ->and($data->controlStock)->toBeTrue();
})->with('providers', ['get']);

test('maps a disabled stock control', function (Provider $provider, string $responseFixture) {
    $response = fixtures()->response()->item()->files($responseFixture);

    match ($provider) {
        Provider::CEGID_VENDUS => $response['stock_control'] = '0',
        Provider::MOLONI => $response['has_stock'] = 0,
    };

    Http::fake(mockResponse($response));

    $data = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($data->controlStock)->toBeFalse();
})->with('providers', ['get']);

test('maps the gross price and the tax', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($responseFixture)));

    $data = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($data->price)->toBe(2460)
        ->and($data->tax)->toBe(ItemTax::NORMAL);
})->with('providers', ['get']);

test('leaves the price and the tax empty when the provider sends none', function (Provider $provider, string $responseFixture) {
    $response = fixtures()->response()->item()->files($responseFixture);

    $response = match ($provider) {
        Provider::CEGID_VENDUS => collect($response)->except(['gross_price', 'tax_id'])->all(),
        Provider::MOLONI => [...$response, 'taxes' => []],
    };

    Http::fake(mockResponse($response));

    $data = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($data->price)->toBeNull()
        ->and($data->tax)->toBeNull();
})->with('providers', ['get']);

test('maps the provider type to the item type', function (Provider $provider, ItemType $expected, string $vendusType, int $moloniType) {
    $response = fixtures()->response()->item()->files('get');

    match ($provider) {
        Provider::CEGID_VENDUS => $response['type_id'] = $vendusType,
        Provider::MOLONI => $response['type'] = $moloniType,
    };

    Http::fake(mockResponse($response));

    $data = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($data->type)->toBe($expected);
})->with('providers')->with([
    [ItemType::Product, 'P', 1],
    [ItemType::Service, 'S', 2],
    [ItemType::Other, 'O', 3],
]);

test('maps the vendus tax item types', function (string $vendusType, ItemType $expected) {
    mockConfiguration(Provider::CEGID_VENDUS);

    $response = fixtures()->response()->item()->files('get');
    $response['type_id'] = $vendusType;

    Http::fake(mockResponse($response));

    $data = Item::get(ItemData::make(['id' => 123]))->execute()->getItem();

    expect($data->type)->toBe($expected);
})->with([
    ['I', ItemType::Tax],
    ['E', ItemType::SpecialTax],
]);

test('maps the stock of each warehouse', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($responseFixture)));

    $item = ItemData::make(['id' => 1542]);

    $data = Item::get($item)->execute()->getItem();

    $stockKey = match ($provider) {
        Provider::CEGID_VENDUS => 'stock',
        Provider::MOLONI => 'warehouses',
    };

    expect($data->stock)->toHaveCount(2)
        ->each->toBeInstanceOf(ItemStockData::class)
        ->and($data->stock->first()->itemId)->toBe(1542)
        ->and($data->stock->first()->warehouseId)->toBe(1)
        ->and($data->stock->first()->quantity)->toBe(40)
        ->and($data->stock->last()->itemId)->toBe(1542)
        ->and($data->stock->last()->warehouseId)->toBe(2)
        ->and($data->stock->last()->quantity)->toBe(2.5)
        ->and($data->getAdditionalData())->not->toHaveKey($stockKey);
})->with('providers', ['get']);
