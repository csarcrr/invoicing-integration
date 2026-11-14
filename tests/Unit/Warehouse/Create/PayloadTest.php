<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

it('builds the create url', function (Provider $provider) {
    $create = Warehouse::create(WarehouseData::make(['name' => 'Loja']));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create->getUrl())->toBe('stores'),
        Provider::MOLONI => expect($create->getUrl())->toBe('warehouses/insert'),
    };
})->with('providers');

it('builds the payload from the warehouse data', function (Provider $provider) {
    $payload = Warehouse::create(WarehouseData::make([
        'name' => 'Armazém Principal',
        'address' => 'Rua Nova de Mil Fontes, 35',
        'city' => 'Setúbal',
        'postalCode' => '1100-234',
    ]))->getPayload()->toArray();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload)->toBe([
            'title' => 'Armazém Principal',
            'address' => 'Rua Nova de Mil Fontes, 35',
            'local' => 'Setúbal',
            'postalcode' => '1100-234',
        ]),
        Provider::MOLONI => expect(collect($payload)->except('code')->all())->toBe([
            'title' => 'Armazém Principal',
            'address' => 'Rua Nova de Mil Fontes, 35',
            'city' => 'Setúbal',
            'zip_code' => '1100-234',
        ]),
    };
})->with('providers');

it('does not send the country to Cegid Vendus', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    $payload = Warehouse::create(WarehouseData::make(['name' => 'Loja', 'country' => 'PT']))->getPayload();

    expect($payload->keys()->all())->toBe(['title']);
});

it('leaves out the fields that are not set', function (Provider $provider) {
    $keys = Warehouse::create(WarehouseData::make(['name' => 'Loja']))->getPayload()->keys()->all();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($keys)->toBe(['title']),
        Provider::MOLONI => expect($keys)->toBe(['title', 'code']),
    };
})->with('providers');

it('generates a random code', function () {
    mockConfiguration(Provider::MOLONI);

    $first = Warehouse::create(WarehouseData::make(['name' => 'Loja']))->getPayload()->get('code');
    $second = Warehouse::create(WarehouseData::make(['name' => 'Loja']))->getPayload()->get('code');

    expect($first)->toBeString()
        ->toHaveLength(8)
        ->and($second)->not->toBe($first);
});
