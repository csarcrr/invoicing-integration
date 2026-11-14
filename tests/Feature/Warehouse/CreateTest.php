<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

test('maps the created warehouse onto WarehouseData', function () {
    Http::fake(mockResponse(fixtures()->response()->warehouse()->files('create')));

    $warehouse = Warehouse::create(WarehouseData::make([
        'name' => 'Armazém Principal',
        'code' => 'AP',
        'isDefault' => true,
    ]))->execute()->getWarehouse();

    expect($warehouse)->toBeInstanceOf(WarehouseData::class)
        ->and($warehouse->id)->toBe(29999)
        ->and($warehouse->name)->toBe('Armazém Principal')
        ->and($warehouse->code)->toBe('AP')
        ->and($warehouse->isDefault)->toBeTrue();
});

test('sends the Moloni country id for the country code', function () {
    Http::fake([
        '*countries/getAll*' => mockResponse(fixtures()->response()->country()->files('response_multiple')),
        '*warehouses/insert*' => mockResponse(fixtures()->response()->warehouse()->files('create')),
    ]);

    Warehouse::create(WarehouseData::make([
        'name' => 'Loja',
        'country' => 'ES',
    ]))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'warehouses/insert')
        && Str::contains($request->body(), 'country_id=70')
        && ! Str::contains($request->body(), 'country='));
});

test('fails with the provider error when the warehouse is not created', function () {
    Http::fake(mockResponse(['valid' => 0, 'message' => 'Invalid code']));

    Warehouse::create(WarehouseData::make(['name' => 'Loja']))->execute();
})->throws(RequestFailedException::class);
