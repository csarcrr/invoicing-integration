<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Spatie\Ray\Payloads\Payload;

it('transforms item data to provider payload', function (Provider $provider, string $fixture) {
    $payload = fixtures()->request()->item()->files($fixture);

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'reference' => 'reference-1'
    ]))->getPayload();

    dd($data);
    expect($data->getPayload())->toMatchArray($payload);
})->with('providers', ['create'])->only();
