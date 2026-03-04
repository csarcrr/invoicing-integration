<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('transforms item data to provider payload', function (Provider $provider, string $fixture) {
    $payload = fixtures()->request()->item()->files($fixture);

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'reference' => 'reference-1',
        'description' => 'Item Description',
        'barcode' => 'barcode-1',
        'category' => CategoryData::make(['id' => 1]),
    ]))->getPayload();
    expect($data->toArray())->toMatchArray($payload);
})->with('providers', ['create'])->only();
