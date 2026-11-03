<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
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
        'tax' => ItemTax::EXEMPT,
        'taxExemptionReason' => TaxExemptionReason::M40,
        'taxExemptionLaw' => TaxExemptionReason::M40->laws()[0],
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
            ->and($payload->get('stock_control'))->toBe('1')
            ->and($payload->get('status'))->toBe('on'),
        Provider::MOLONI => expect($payload->get('type'))->toBe(1)
            ->and($payload->get('has_stock'))->toBe(1),
    };
})->with('providers');
