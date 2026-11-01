<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetTaxIdException;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('sends the price and the tax of a taxed item', function (Provider $provider) {
    $payload = Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
        'price' => 2460,
        'tax' => ItemTax::NORMAL,
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('gross_price'))->toBe(24.6)
            ->and($payload->get('tax_id'))->toBe('NOR')
            ->and($payload->has('tax_exemption'))->toBeFalse(),
        Provider::MOLONI => expect($payload->get('price'))->toBe(20.0)
            ->and($payload->get('taxes'))->toBe([[
                'tax_id' => 1,
                'value' => 23.0,
                'order' => 0,
                'cumulative' => 0,
            ]])
            ->and($payload->has('exemption_reason'))->toBeFalse(),
    };
})->with('providers');

it('sends the price of an exempt item', function (Provider $provider) {
    $payload = Item::create(ItemData::make([
        'category' => CategoryData::make(['id' => 1]),
        'price' => 2000,
        'tax' => ItemTax::EXEMPT,
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('gross_price'))->toBe(20)
            ->and($payload->get('tax_id'))->toBe('ISE'),
        Provider::MOLONI => expect($payload->get('price'))->toBe(20.0)
            ->and($payload->has('taxes'))->toBeFalse(),
    };
})->with('providers');

it('fails when the moloni tax is not configured', function () {
    mockConfiguration(Provider::MOLONI);

    config()->set('invoicing-integration.providers.'.Provider::MOLONI->value.'.taxes', []);

    Item::create(ItemData::make([
        'category' => CategoryData::make(['id' => 1]),
        'tax' => ItemTax::NORMAL,
    ]))->getPayload();
})->throws(CouldNotGetTaxIdException::class);
