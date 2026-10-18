<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\MissingCategoryException;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('transforms item search filters to provider payload', function (Provider $provider, string $fixture) {
    if ($provider === Provider::MOLONI) {
        $this->markTestSkipped('Moloni does not support item search filters.');
    }

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

it('fails when moloni has no category to list', function () {
    mockConfiguration(Provider::MOLONI);

    Item::find()->getPayload();
})->throws(MissingCategoryException::class);
