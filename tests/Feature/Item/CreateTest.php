<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Facades\Item;

test('creates an item', function (Provider $provider, string $fixture) {
    $payload = fixtures()->response()->item()->files($fixture);

    Http::fake(mockResponse($payload));

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'reference' => 'reference-1',
        'description' => 'Item Description',
        'barcode' => 'barcode-1',
        'price' => 2000,
        'tax' => CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax::NORMAL,
    ]))->execute()->getItem();

    expect($data->id)->toBeInt()
        ->and($data->name)->toBeString();
})->with('providers', ['create']);
