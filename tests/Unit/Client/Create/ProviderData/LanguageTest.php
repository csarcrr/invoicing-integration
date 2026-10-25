<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('sends the client language', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'language' => ['code' => 'en'],
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('language_id'))->toBe(2),
        Provider::CEGID_VENDUS => expect($payload->has('language_id'))->toBeFalse()
            ->and($payload->has('language'))->toBeFalse(),
    };
})->with('providers');

it('sends portuguese as the moloni language when the client has none', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('language_id'))->toBe(1),
        Provider::CEGID_VENDUS => expect($payload->has('language_id'))->toBeFalse(),
    };
})->with('providers');
