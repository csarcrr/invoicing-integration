<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetTaxIdException;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Support\Facades\Http;

it('sends a zero price', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'price' => 0]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('gross_price'))->toBe(0),
        Provider::MOLONI => expect($payload->get('price'))->toBe(0.0),
    };
})->with('providers');

it('sends the price using the current tax', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'price' => 2460]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('gross_price'))->toBe(24.6)
            ->and($payload->has('tax_id'))->toBeFalse(),
        Provider::MOLONI => expect($payload->get('price'))->toBe(20.0)
            ->and($payload->get('taxes'))->toBe([['tax_id' => 1, 'value' => 4.6, 'order' => 0, 'cumulative' => 0]]),
    };
})->with('providers');

it('keeps the gross price when only the tax changes', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'tax' => ItemTax::REDUCED]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('tax_id'))->toBe(ItemTax::REDUCED->vendus())
            ->and($payload->has('gross_price'))->toBeFalse(),
        Provider::MOLONI => expect($payload->get('price'))->toBe(23.2075)
            ->and($payload->get('taxes'))->toBe([['tax_id' => 3, 'value' => 6.0, 'order' => 0, 'cumulative' => 0]]),
    };
})->with('providers');

it('keeps the gross price when the tax changes to exempt', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'tax' => ItemTax::EXEMPT]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('tax_id'))->toBe('ISE'),
        Provider::MOLONI => expect($payload->get('price'))->toBe(24.6)
            ->and($payload->get('taxes'))->toBe([['tax_id' => 5, 'value' => 0.0, 'order' => 0, 'cumulative' => 0]]),
    };
})->with('providers');

it('sends the price with the new tax when both change', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'price' => 1060, 'tax' => ItemTax::REDUCED]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('gross_price'))->toBe(10.6)
            ->and($payload->get('tax_id'))->toBe(ItemTax::REDUCED->vendus()),
        Provider::MOLONI => expect($payload->get('price'))->toBe(10.0)
            ->and($payload->get('taxes'))->toBe([['tax_id' => 3, 'value' => 6.0, 'order' => 0, 'cumulative' => 0]]),
    };
})->with('providers');

it('sends the price without tax when both change to exempt', function () {
    mockConfiguration(Provider::MOLONI);
    fakeItemUpdate(Provider::MOLONI);

    $payload = Item::update(ItemData::make(['id' => 1542, 'price' => 1000, 'tax' => ItemTax::EXEMPT]))->getPayload();

    expect($payload->get('price'))->toBe(10.0)
        ->and($payload->get('taxes'))->toBe([['tax_id' => 5, 'value' => 0.0, 'order' => 0, 'cumulative' => 0]]);
});

it('sends the exemption reason', function (Provider $provider) {
    fakeItemUpdate($provider);

    $law = TaxExemptionReason::M04->laws()[0];

    $payload = Item::update(ItemData::make([
        'id' => 1542,
        'tax' => ItemTax::EXEMPT,
        'taxExemptionReason' => TaxExemptionReason::M04,
        'taxExemptionLaw' => $law,
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('tax_exemption'))->toBe('M04')
            ->and($payload->get('tax_exemption_law'))->toBe($law),
        Provider::MOLONI => expect($payload->get('exemption_reason'))->toBe('M04')
            ->and($payload->has('tax_exemption_law'))->toBeFalse(),
    };
})->with('providers');

it('resolves the moloni exemption reason from the tax', function (array $data, string $expected) {
    mockConfiguration(Provider::MOLONI);
    fakeItemUpdate(Provider::MOLONI, 'get_with_relations');

    $payload = Item::update(ItemData::make(['id' => 1542, ...$data]))->getPayload();

    expect($payload->get('exemption_reason'))->toBe($expected);
})->with([
    'tax is not exempt' => [['tax' => ItemTax::NORMAL], ''],
    'tax is exempt without reason' => [['tax' => ItemTax::EXEMPT], 'M07'],
    'no tax' => [[], 'M07'],
]);

it('fails when the moloni tax is not configured, without any request', function (ItemTax $tax) {
    mockConfiguration(Provider::MOLONI);
    fakeItemUpdate(Provider::MOLONI);
    config()->set('invoicing-integration.providers.'.Provider::MOLONI->value.'.taxes', []);

    expect(fn () => Item::update(ItemData::make(['id' => 1542, 'tax' => $tax]))->execute())
        ->toThrow(CouldNotGetTaxIdException::class);

    Http::assertNothingSent();
})->with([ItemTax::NORMAL, ItemTax::EXEMPT]);
