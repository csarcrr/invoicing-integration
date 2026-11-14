<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('maps the created warehouse onto WarehouseData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->warehouse()->files('create')));

    $create = Warehouse::create(WarehouseData::make([
        'name' => 'Armazém Principal',
        'address' => 'Rua Nova de Mil Fontes, 35',
    ]));
    $code = $create->getPayload()->get('code');

    $warehouse = $create->execute()->getWarehouse();

    expect($warehouse)->toBeInstanceOf(WarehouseData::class)
        ->and($warehouse->id)->toBe(29999)
        ->and($warehouse->name)->toBe('Armazém Principal')
        ->and($warehouse->address)->toBe('Rua Nova de Mil Fontes, 35');

    match ($provider) {
        Provider::CEGID_VENDUS => expect($warehouse->getAdditionalData()['type'])->toBe('store'),
        Provider::MOLONI => expect($warehouse->getAdditionalData()['code'])->toBe($code),
    };
})->with('providers');

test('sends the Moloni country id for the country code', function () {
    mockConfiguration(Provider::MOLONI);

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

test('fails with the provider error when the Moloni warehouse is not created', function () {
    mockConfiguration(Provider::MOLONI);

    Http::fake(mockResponse(['valid' => 0, 'message' => 'Invalid code']));

    Warehouse::create(WarehouseData::make(['name' => 'Loja']))->execute();
})->throws(RequestFailedException::class);

test('fails with the provider error when the Cegid Vendus store is rejected', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'Poorly filled parameters']]], 403));

    Warehouse::create(WarehouseData::make(['name' => 'Loja']))->execute();
})->throws(RequestFailedException::class);
