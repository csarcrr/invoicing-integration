<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

it('builds the create url', function () {
    expect(Warehouse::create(WarehouseData::make(['name' => 'Loja']))->getUrl())->toBe('warehouses/insert');
});

it('builds the payload from the warehouse data', function () {
    $payload = Warehouse::create(WarehouseData::make([
        'name' => 'Armazém Principal',
        'code' => 'AP',
        'address' => 'Rua Nova de Mil Fontes, 35',
        'city' => 'Setúbal',
        'postalCode' => '1100-234',
        'isDefault' => true,
    ]))->getPayload()->toArray();

    expect($payload)->toBe([
        'title' => 'Armazém Principal',
        'code' => 'AP',
        'address' => 'Rua Nova de Mil Fontes, 35',
        'city' => 'Setúbal',
        'zip_code' => '1100-234',
        'is_default' => 1,
    ]);
});

it('sends is_default as 0 when the warehouse is not the default', function () {
    $payload = Warehouse::create(WarehouseData::make(['name' => 'Loja', 'isDefault' => false]))->getPayload();

    expect($payload->get('is_default'))->toBe(0);
});

it('leaves out the fields that are not set', function () {
    expect(Warehouse::create(WarehouseData::make(['name' => 'Loja']))->getPayload()->toArray())->toBe(['title' => 'Loja']);
});
