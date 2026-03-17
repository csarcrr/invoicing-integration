<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Support\Facades\Http;

test('an item get request is successful', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->item()->files($responseFixture)));

    $item = ItemData::make(['id' => 123]);

    $data = Item::get($item)->execute()->getItem();

    expect($data->name)->toBeString()
        ->and($data->description)->toBeString()
        ->and($data->getAdditionalData())->not->toBeEmpty();

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
