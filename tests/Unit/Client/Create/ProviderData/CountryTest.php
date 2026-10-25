<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('sends the client country', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'country' => 'ES',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('country_id'))->toBe(70)
            ->and($payload->has('country'))->toBeFalse(),
        Provider::CEGID_VENDUS => expect($payload->get('country'))->toBe('ES'),
    };
})->with('providers');

it('sends portugal as the moloni country when the client has none', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('country_id'))->toBe(1),
        Provider::CEGID_VENDUS => expect($payload->has('country_id'))->toBeFalse(),
    };
})->with('providers');
