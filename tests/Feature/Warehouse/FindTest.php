<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;
use Illuminate\Support\Facades\Http;

test('maps the provider response onto a list of WarehouseData', function () {
    Http::fake(mockResponse(fixtures()->response()->warehouse()->files('response_multiple')));

    $list = Warehouse::find()->execute()->getList();

    expect($list)->toHaveCount(2)
        ->and($list->first())->toBeInstanceOf(WarehouseData::class)
        ->and($list->pluck('id')->toArray())->toBe([29999, 19999])
        ->and($list->pluck('name')->toArray())->toBe(['Armazém Principal', 'Loja']);
})->with('providers');

test('keeps the extra response fields as additional data', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->warehouse()->files('response_multiple')));

    $warehouse = Warehouse::find()->execute()->getList()->first();

    expect($warehouse->getAdditionalData())->toMatchArray(match ($provider) {
        Provider::CEGID_VENDUS => ['type' => 'warehouse'],
        Provider::MOLONI => ['code' => 'AP'],
    });
})->with('providers');

test('returns an empty list when the provider has no warehouses', function () {
    Http::fake(mockResponse([]));

    expect(Warehouse::find()->execute()->getList())->toBeEmpty();
})->with('providers');

test('returns an empty list when Cegid Vendus reports no data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    expect(Warehouse::find()->execute()->getList())->toBeEmpty();
});
