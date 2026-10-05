<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Support\Str;

it('uses the client id as the number', function (Provider $provider) {
    $payload = Client::create(ClientData::from([
        'id' => 'CLIENT-1',
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

it('builds the number from the vat and a random string when no id is given', function (Provider $provider) {
    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('number'))->toStartWith('223098091')
            ->and(Str::length($payload->get('number')))->toBe(17),
        Provider::CEGID_VENDUS => expect($payload->has('number'))->toBeFalse()
            ->and($payload->has('id'))->toBeFalse(),
    };
})->with('providers');
