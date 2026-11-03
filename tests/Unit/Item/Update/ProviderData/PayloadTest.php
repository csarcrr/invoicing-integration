<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Unit;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\CouldNotGetUnitIdException as VendusCouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetUnitIdException as MoloniCouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Support\Facades\Http;

it('sends only the set fields and keeps the current values', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'name' => 'New Title']))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->toArray())->toBe(['title' => 'New Title']),
        Provider::MOLONI => expect($payload->toArray())->toEqual([
            'product_id' => 1542,
            'category_id' => 1,
            'type' => 1,
            'name' => 'New Title',
            'summary' => 'A small product description used for testing purposes.',
            'reference' => 'reference-1',
            'ean' => 'barcode-1',
            'price' => 20,
            'unit_id' => 3,
            'has_stock' => 1,
            'visibility_id' => 1,
            'exemption_reason' => '',
            'taxes' => [['tax_id' => 1, 'value' => 4.6, 'order' => 0, 'cumulative' => 0]],
        ]),
    };
})->with('providers');

it('maps each set field to the provider field', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make([
        'id' => 1542,
        'name' => 'New Title',
        'description' => 'New Description',
        'reference' => 'new-reference',
        'barcode' => 'new-barcode',
        'type' => ItemType::Service,
        'category' => CategoryData::make(['id' => 5]),
        'unit' => Unit::KG,
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->toArray())->toMatchArray([
            'title' => 'New Title',
            'description' => 'New Description',
            'reference' => 'new-reference',
            'barcode' => 'new-barcode',
            'type_id' => 'S',
            'category_id' => 5,
            'unit_id' => 19999,
        ]),
        Provider::MOLONI => expect($payload->toArray())->toMatchArray([
            'product_id' => 1542,
            'name' => 'New Title',
            'summary' => 'New Description',
            'reference' => 'new-reference',
            'ean' => 'new-barcode',
            'type' => 2,
            'category_id' => 5,
            'unit_id' => 19999,
        ]),
    };
})->with('providers');

it('sends empty strings to clear the description and the barcode', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'description' => '', 'barcode' => '']))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('description'))->toBe('')
            ->and($payload->get('barcode'))->toBe(''),
        Provider::MOLONI => expect($payload->get('summary'))->toBe('')
            ->and($payload->get('ean'))->toBe(''),
    };
})->with('providers');

it('sends a false stock control', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'controlStock' => false]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('stock_control'))->toBe('0'),
        Provider::MOLONI => expect($payload->get('has_stock'))->toBe(0),
    };
})->with('providers');

it('sends a true stock control', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'controlStock' => true]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('stock_control'))->toBe('1'),
        Provider::MOLONI => expect($payload->get('has_stock'))->toBe(1),
    };
})->with('providers');

it('never sends the stock or the invoice line fields', function (Provider $provider) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make([
        'id' => 1542,
        'quantity' => 2,
        'note' => 'A note',
        'percentageDiscount' => 10,
        'amountDiscount' => 50,
        'stock' => [['itemId' => 1542, 'warehouseId' => 1, 'quantity' => 5]],
    ]))->getPayload();

    expect($payload->keys()->intersect(['stock', 'warehouses', 'qty', 'note', 'discount']))->toBeEmpty();
})->with('providers');

it('sends the enabled state', function (Provider $provider, bool $enabled, string $vendusStatus, int $moloniVisibility) {
    fakeItemUpdate($provider);

    $payload = Item::update(ItemData::make(['id' => 1542, 'enabled' => $enabled]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('status'))->toBe($vendusStatus),
        Provider::MOLONI => expect($payload->get('visibility_id'))->toBe($moloniVisibility),
    };
})->with('providers')->with([
    [true, 'on', 1],
    [false, 'off', 0],
]);

it('does not send moloni read-only or nested fields', function () {
    mockConfiguration(Provider::MOLONI);
    fakeItemUpdate(Provider::MOLONI);

    $payload = Item::update(ItemData::make(['id' => 1542]))->getPayload();

    expect($payload->keys()->intersect(['category', 'image', 'stock', 'warehouses']))->toBeEmpty()
        ->and($payload->get('taxes')[0])->not->toHaveKeys(['tax', 'product_id']);
});

it('carries the moloni visibility so a hidden product stays hidden', function () {
    mockConfiguration(Provider::MOLONI);

    $current = [...fixtures()->response()->item()->files('get'), 'visibility_id' => 0];

    Http::fakeSequence()
        ->pushResponse(mockResponse($current))
        ->pushResponse(mockResponse(fixtures()->response()->item()->files('update')));

    $payload = Item::update(ItemData::make(['id' => 1542, 'name' => 'New Title']))->getPayload();

    expect($payload->get('visibility_id'))->toBe(0);
});

it('reduces the moloni suppliers and properties to their documented keys', function () {
    mockConfiguration(Provider::MOLONI);
    fakeItemUpdate(Provider::MOLONI, 'get_with_relations');

    $payload = Item::update(ItemData::make(['id' => 1542]))->getPayload();

    expect($payload->get('suppliers'))->toBe([['supplier_id' => 7, 'cost_price' => 9.5, 'reference' => 11]])
        ->and($payload->get('properties'))->toBe([['property_id' => 8, 'value' => 'Blue']])
        ->and($payload->get('minimum_stock'))->toBe(5)
        ->and($payload->get('pos_favorite'))->toBe(1)
        ->and($payload->get('at_product_category'))->toBe('M');
});

it('fails when the unit is not mapped, without any request', function (Provider $provider) {
    fakeItemUpdate($provider);
    config()->set('invoicing-integration.providers.'.$provider->value.'.units', []);

    $expectedException = match ($provider) {
        Provider::CEGID_VENDUS => VendusCouldNotGetUnitIdException::class,
        Provider::MOLONI => MoloniCouldNotGetUnitIdException::class,
    };

    expect(fn () => Item::update(ItemData::make(['id' => 1542, 'unit' => Unit::KG]))->execute())
        ->toThrow($expectedException);

    Http::assertNothingSent();
})->with('providers');

it('fails when no id is set, without any request', function (Provider $provider) {
    fakeItemUpdate($provider);

    expect(fn () => Item::update(ItemData::make(['name' => 'New Title']))->execute())
        ->toThrow(InvalidArgumentException::class, 'Item ID is required.');

    Http::assertNothingSent();
})->with('providers');
