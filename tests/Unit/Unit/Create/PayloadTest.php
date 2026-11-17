<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Unit;

it('builds the create url', function (Provider $provider) {
    $create = Unit::create(UnitData::make(['name' => 'Kilograma', 'shortName' => 'Kg']));

    match ($provider) {
        Provider::VENDUS => expect($create->getUrl())->toBe('products/units'),
        Provider::MOLONI => expect($create->getUrl())->toBe('measurementUnits/insert'),
    };
})->with('providers');

it('builds the payload with a short name', function (Provider $provider) {
    $payload = Unit::create(UnitData::make(['name' => 'Kilograma', 'shortName' => 'Kg']))->getPayload();

    match ($provider) {
        Provider::VENDUS => expect($payload->toArray())->toBe(['title' => 'Kilograma']),
        Provider::MOLONI => expect($payload->toArray())->toBe(['name' => 'Kilograma', 'short_name' => 'Kg']),
    };
})->with('providers');

it('builds the payload without a short name', function (Provider $provider) {
    $payload = Unit::create(UnitData::make(['name' => 'Kilograma']))->getPayload();

    match ($provider) {
        Provider::VENDUS => expect($payload->toArray())->toBe(['title' => 'Kilograma']),
        Provider::MOLONI => expect($payload->toArray())->toBe(['name' => 'Kilograma']),
    };
})->with('providers');
