<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\MissingCategoryException;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('transforms item search filters to provider payload', function (Provider $provider, string $fixture) {
    $payload = fixtures()->request()->item()->files($fixture);

    $data = Item::find(ItemData::from([
        'name' => 'Item Title',
        'reference' => 'reference-1',
        'barcode' => 'barcode-1',
    ]));

    expect($data->getPayload())->toMatchArray($payload);
})->with('providers', ['search']);

it('builds the category and pagination payload for the first page', function (Provider $provider) {
    $find = Item::find(ItemData::make(['category' => CategoryData::make(['id' => 12])]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find->getPayload()->toArray())->toBe(['page' => 1]),
        Provider::MOLONI => expect($find->getPayload()->toArray())->toBe(['category_id' => 12, 'qty' => 50, 'offset' => 0]),
    };
})->with('providers');

it('moves the offset when changing page', function (Provider $provider) {
    $find = Item::find(ItemData::make(['category' => CategoryData::make(['id' => 12])]))->next();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find->getPayload()->toArray())->toBe(['page' => 2]),
        Provider::MOLONI => expect($find->getPayload()->toArray())->toBe(['category_id' => 12, 'qty' => 50, 'offset' => 50]),
    };
})->with('providers');

it('searches moloni by reference before name', function () {
    mockConfiguration(Provider::MOLONI);

    $find = Item::find(ItemData::make(['name' => 'Item Title', 'reference' => 'reference-1']));

    expect($find->getPayload()->toArray())->toBe(['search' => 'reference-1', 'qty' => 50, 'offset' => 0]);
});

it('searches moloni by name without a category', function () {
    mockConfiguration(Provider::MOLONI);

    $find = Item::find(ItemData::make(['name' => 'Item Title']));

    expect($find->getPayload()->toArray())->toBe(['search' => 'Item Title', 'qty' => 50, 'offset' => 0]);
});

it('fails when moloni has no category to list', function () {
    mockConfiguration(Provider::MOLONI);

    Item::find()->getPayload();
})->throws(MissingCategoryException::class);
