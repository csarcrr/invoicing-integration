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
        'address' => 'Rua Nova de Mil Fontes, 35',
        'city' => 'Setúbal',
        'postalCode' => '1100-234',
    ]))->getPayload()->toArray();

    expect(collect($payload)->except('code')->all())->toBe([
        'title' => 'Armazém Principal',
        'address' => 'Rua Nova de Mil Fontes, 35',
        'city' => 'Setúbal',
        'zip_code' => '1100-234',
    ]);
});

it('generates a random code', function () {
    $first = Warehouse::create(WarehouseData::make(['name' => 'Loja']))->getPayload()->get('code');
    $second = Warehouse::create(WarehouseData::make(['name' => 'Loja']))->getPayload()->get('code');

    expect($first)->toBeString()
        ->toHaveLength(8)
        ->and($second)->not->toBe($first);
});

it('leaves out the fields that are not set', function () {
    expect(Warehouse::create(WarehouseData::make(['name' => 'Loja']))->getPayload()->keys()->all())->toBe(['title', 'code']);
});
