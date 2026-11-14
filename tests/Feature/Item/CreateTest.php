<?php

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Unit;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('creates an item', function (Provider $provider) {
    fakeItemCreate($provider);

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'reference' => 'reference-1',
        'description' => 'Item Description',
        'barcode' => 'barcode-1',
        'category' => CategoryData::make(['id' => 1]),
        'price' => 2000,
        'tax' => ItemTax::NORMAL,
    ]))->execute()->getItem();

    expect($data->id)->toBe(1542)
        ->and($data->name)->toBeString()
        ->and($data->getAdditionalData())->not->toBeEmpty();
})->with('providers');

test('reads the created item from the provider', function (Provider $provider) {
    fakeItemCreate($provider);

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
    ]))->execute()->getItem();

    expect($data->id)->toBe(1542)
        ->and($data->reference)->toBe('reference-1')
        ->and($data->description)->toBe('A small product description used for testing purposes.')
        ->and($data->barcode)->toBe('barcode-1')
        ->and($data->price)->toBe(2460)
        ->and($data->tax)->toBe(ItemTax::NORMAL)
        ->and($data->type)->toBe(ItemType::Product)
        ->and($data->unit)->toBe(Unit::KG)
        ->and($data->controlStock)->toBeTrue()
        ->and($data->enabled)->toBeTrue();
})->with('providers');

test('reads an item created as disabled', function (Provider $provider) {
    fakeItemCreate($provider, match ($provider) {
        Provider::CEGID_VENDUS => ['status' => 'off'],
        Provider::MOLONI => ['visibility_id' => 0],
    });

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
        'enabled' => false,
    ]))->execute()->getItem();

    expect($data->enabled)->toBeFalse();
})->with('providers');

test('reads the exemption law only from cegid vendus', function (Provider $provider) {
    $law = 'Artigo 6.º n.º 6 alínea a) do CIVA, a contrário';

    fakeItemCreate($provider, match ($provider) {
        Provider::CEGID_VENDUS => ['tax' => ['id' => 'ISE', 'exemption' => 'M40', 'exemption_law' => $law]],
        Provider::MOLONI => [
            'exemption_reason' => 'M40',
            'taxes' => [[
                'product_id' => 1542,
                'tax_id' => 5,
                'value' => 0,
                'order' => 0,
                'cumulative' => 0,
                'tax' => ['tax_id' => 5, 'vat_type' => 'ISE', 'value' => 0],
            ]],
        ],
    });

    $data = Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
        'tax' => ItemTax::EXEMPT,
        'taxExemptionReason' => 'M40',
        'taxExemptionLaw' => $law,
    ]))->execute()->getItem();

    expect($data->tax)->toBe(ItemTax::EXEMPT)
        ->and($data->taxExemptionReason)->toBe('M40')
        ->and($data->taxExemptionLaw)->toBe(match ($provider) {
            Provider::CEGID_VENDUS => $law,
            Provider::MOLONI => null,
        });
})->with('providers');

test('sends the item to the provider', function (Provider $provider) {
    fakeItemCreate($provider);

    Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
    ]))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), 'products')
            && $request['category_id'] === 1,
        Provider::MOLONI => Str::contains($request->url(), 'products/insert')
            && Str::contains($request->body(), 'category_id=1')
            && Str::contains($request->body(), 'company_id=123456'),
    });
})->with('providers');

test('reads the created moloni item back with getOne', function () {
    mockConfiguration(Provider::MOLONI);
    fakeItemCreate(Provider::MOLONI);

    Item::create(ItemData::make([
        'name' => 'Item Title',
        'category' => CategoryData::make(['id' => 1]),
    ]))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'products/getOne')
        && Str::contains($request->body(), 'product_id=1542')
        && Str::contains($request->body(), 'with_invisible=1'));
});

test('fails when the provider rejects the item', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse([
            'errors' => [
                ['code' => 'P003', 'message' => 'Param unit_id is required for this action. Please add it.'],
            ],
        ], 400),
        Provider::MOLONI => mockResponse([
            ['code' => '1 exemption_reason', 'description' => "Field 'exemption_reason' is required"],
        ]),
    });

    expect(fn () => Item::create(ItemData::make([
        'name' => 'Item Title',
        'reference' => 'reference-1',
        'category' => CategoryData::make(['id' => 1]),
        'price' => 2000,
    ]))->execute())->toThrow(RequestFailedException::class, match ($provider) {
        Provider::CEGID_VENDUS => 'P003 - Param unit_id is required for this action. Please add it.',
        Provider::MOLONI => "1 exemption_reason - Field 'exemption_reason' is required",
    });
})->with('providers');
