<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Validation\ValidationException;

it('sends the client discount, credit limit and delivery method', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'discount' => 12.5,
        'creditLimit' => 500.0,
        'deliveryMethodId' => 3,
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('discount'))->toBe(12.5)
            ->and($payload->get('credit_limit'))->toBe(500.0)
            ->and($payload->get('delivery_method_id'))->toBe(3),
        Provider::CEGID_VENDUS => expect($payload->has('discount'))->toBeFalse()
            ->and($payload->has('credit_limit'))->toBeFalse()
            ->and($payload->has('delivery_method_id'))->toBeFalse(),
    };
})->with('providers');

it('sends zero as the moloni discount, credit limit and delivery method when the client has none', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('discount'))->toBe(0.0)
            ->and($payload->get('credit_limit'))->toBe(0.0)
            ->and($payload->get('delivery_method_id'))->toBe(0),
        Provider::CEGID_VENDUS => expect($payload->has('discount'))->toBeFalse()
            ->and($payload->has('credit_limit'))->toBeFalse()
            ->and($payload->has('delivery_method_id'))->toBeFalse(),
    };
})->with('providers');

it('rejects invalid discount, credit limit and delivery method', function (array $data) {
    ClientData::make($data);
})->with([
    'discount above 100' => [['discount' => 101]],
    'negative discount' => [['discount' => -1]],
    'negative credit limit' => [['creditLimit' => -1]],
    'negative delivery method' => [['deliveryMethodId' => -1]],
])->throws(ValidationException::class);
