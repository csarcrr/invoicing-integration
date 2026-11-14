<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Unit;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('transforms item data to provider payload', function (Provider $provider, string $fixture) {
    $payload = fixtures()->request()->item()->files($fixture);

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'reference' => 'reference-1',
        'description' => 'Item Description',
        'barcode' => 'barcode-1',
        'category' => CategoryData::make(['id' => 1]),
        'price' => 2000,
        'tax' => CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax::EXEMPT,
        'taxExemptionReason' => 'M40',
        'taxExemptionLaw' => 'Artigo 6.º n.º 6 alínea a) do CIVA, a contrário',
        'unit' => Unit::KG,
    ]))->getPayload();

    expect($data->toArray())->toMatchArray($payload);
})->with('providers', ['create']);

it('sends the creation defaults for omitted fields', function (Provider $provider) {
    $payload = Item::create(ItemData::make([
        'category' => CategoryData::make(['id' => 1]),
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('type_id'))->toBe('P')
            ->and($payload->get('stock')['control'])->toBe('1')
            ->and($payload->get('status'))->toBe('on'),
        Provider::MOLONI => expect($payload->get('type'))->toBe(1)
            ->and($payload->get('has_stock'))->toBe(1),
    };
})->with('providers');

it('sends whether the item is enabled', function (Provider $provider, bool $enabled, string $vendusStatus, int $moloniVisibility) {
    $payload = Item::create(ItemData::make([
        'category' => CategoryData::make(['id' => 1]),
        'enabled' => $enabled,
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('status'))->toBe($vendusStatus),
        Provider::MOLONI => expect($payload->get('visibility_id'))->toBe($moloniVisibility),
    };
})->with('providers')->with([
    [true, 'on', 1],
    [false, 'off', 0],
]);
