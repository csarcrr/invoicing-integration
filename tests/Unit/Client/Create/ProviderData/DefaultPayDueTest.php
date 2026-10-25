<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('sends the client default pay due', function (Provider $provider) {
    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'defaultPayDue' => 30,
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('payment_day'))->toBe(30),
        Provider::CEGID_VENDUS => expect($payload->get('default_pay_due'))->toBe('30'),
    };
})->with('providers');

it('sends 15 days as the moloni payment day when the client has no default pay due', function (Provider $provider) {
    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('payment_day'))->toBe(15),
        Provider::CEGID_VENDUS => expect($payload->has('default_pay_due'))->toBeFalse(),
    };
})->with('providers');

it('sends now as the cegid vendus default pay due when the client default pay due is zero', function (Provider $provider) {
    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'defaultPayDue' => 0,
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('payment_day'))->toBe(0),
        Provider::CEGID_VENDUS => expect($payload->get('default_pay_due'))->toBe('now'),
    };
})->with('providers');
