<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Validation\ValidationException;

it('sends the client payment method, payment day and salesman', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'paymentMethodId' => 2,
        'paymentDay' => 10,
        'salesmanId' => 4,
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('payment_method_id'))->toBe(2)
            ->and($payload->get('payment_day'))->toBe(10)
            ->and($payload->get('salesman_id'))->toBe(4),
        Provider::CEGID_VENDUS => expect($payload->has('payment_method_id'))->toBeFalse()
            ->and($payload->has('payment_day'))->toBeFalse()
            ->and($payload->has('salesman_id'))->toBeFalse(),
    };
})->with('providers');

it('does not send the payment method, payment day and salesman when the client has none', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    expect($payload->has('payment_method_id'))->toBeFalse()
        ->and($payload->has('payment_day'))->toBeFalse()
        ->and($payload->has('salesman_id'))->toBeFalse();
})->with('providers');

it('rejects invalid payment method, payment day and salesman', function (array $data) {
    ClientData::make($data);
})->with([
    'negative payment method' => [['paymentMethodId' => -1]],
    'negative payment day' => [['paymentDay' => -1]],
    'negative salesman' => [['salesmanId' => -1]],
])->throws(ValidationException::class);
