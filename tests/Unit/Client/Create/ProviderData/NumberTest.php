<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('uses the reference as the number', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'reference' => 'CLIENT-1',
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('number'))->toBe('CLIENT-1'),
        Provider::CEGID_VENDUS => expect($payload->has('number'))->toBeFalse()
            ->and($payload->has('id'))->toBeFalse(),
    };
})->with('providers');

it('does not use the id as the number when no reference is given', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'id' => 'CLIENT-1',
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->has('number'))->toBeFalse(),
        Provider::CEGID_VENDUS => expect($payload->has('number'))->toBeFalse()
            ->and($payload->has('id'))->toBeFalse(),
    };
})->with('providers');
