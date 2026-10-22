<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\ItemStockData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Support\Facades\Http;

test('an item get request is successful', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($responseFixture)));

    $item = ItemData::make(['id' => 123]);

    $data = Item::get($item)->execute()->getItem();

    [$reference, $barcode] = match ($provider) {
        Provider::CEGID_VENDUS => ['reference-1', 'barcode-1'],
        Provider::MOLONI => ['WBH-2026-BLK', '5601234567890'],
    };

    expect($data->name)->toBeString()->not->toBeEmpty()
        ->and($data->description)->toBe('A small product description used for testing purposes.')
        ->and($data->reference)->toBe($reference)
        ->and($data->barcode)->toBe($barcode)
        ->and($data->getAdditionalData())->not->toBeEmpty()
        ->and($data->getAdditionalData())->not->toHaveKeys(['reference', 'barcode']);

    Http::assertSentCount(1);
})->with('providers', ['get']);

test('supported properties are not filled in additional data', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($responseFixture)));

    $item = ItemData::make(['id' => 123]);

    $data = Item::get($item)->execute()->getItem();

    expect($data->getAdditionalData())
        ->not->toHaveKey('title')
        ->not->toHaveKey('description');
})->with('providers', ['get']);

test('maps the stock of each warehouse', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($responseFixture)));

    $item = ItemData::make(['id' => 1542]);

    $data = Item::get($item)->execute()->getItem();

    [$secondQuantity, $stockKey] = match ($provider) {
        Provider::CEGID_VENDUS => [2, 'stock'],
        Provider::MOLONI => [2.5, 'warehouses'],
    };

    expect($data->stock)->toHaveCount(2)
        ->each->toBeInstanceOf(ItemStockData::class)
        ->and($data->stock->first()->itemId)->toBe(1542)
        ->and($data->stock->first()->warehouseId)->toBe(1)
        ->and($data->stock->first()->quantity)->toBe(40)
        ->and($data->stock->last()->itemId)->toBe(1542)
        ->and($data->stock->last()->warehouseId)->toBe(2)
        ->and($data->stock->last()->quantity)->toBe($secondQuantity)
        ->and($data->getAdditionalData())->not->toHaveKey($stockKey);
})->with('providers', ['get']);
