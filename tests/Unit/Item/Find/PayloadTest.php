<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('transforms item search filters to provider payload', function (Provider $provider, string $fixture) {
    if ($provider === Provider::MOLONI) {
        $this->markTestSkipped('Find is not supported by Moloni yet.');
    }

    $payload = fixtures()->request()->item()->files($fixture);

    $data = Item::find(ItemData::from([
        'name' => 'Item Title',
        'reference' => 'reference-1',
        'barcode' => 'barcode-1',
    ]));

    expect($data->getPayload())->toMatchArray($payload);
})->with('providers', ['search']);
