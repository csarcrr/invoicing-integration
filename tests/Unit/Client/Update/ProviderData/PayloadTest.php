<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Support\Facades\Http;

it('sends every client field', function (Provider $provider) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));
    }

    $payload = Client::update(ClientData::from([
        'id' => '99999',
        'reference' => 'CLIENT-1',
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'postalCode' => '4100-100',
        'city' => 'Porto',
        'country' => 'ES',
        'phone' => '222333444',
        'email' => 'alberto.albertino@aa.pt',
        'notes' => 'Client Notes',
        'defaultPayDue' => DueDateTerm::Days30,
        'status' => 'inactive',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->toArray())->toMatchArray([
            'customer_id' => '99999',
            'number' => 'CLIENT-1',
            'name' => 'Alberto Albertino',
            'vat' => '223098091',
            'address' => 'Rua das Flores 125',
            'zip_code' => '4100-100',
            'city' => 'Porto',
            'country_id' => 70,
            'phone' => '222333444',
            'email' => 'alberto.albertino@aa.pt',
            'notes' => 'Client Notes',
            'maturity_date_id' => 4322,
        ])->and($payload->has('status'))->toBeFalse(),
        Provider::CEGID_VENDUS => expect($payload->toArray())->toMatchArray([
            'external_reference' => 'CLIENT-1',
            'name' => 'Alberto Albertino',
            'fiscal_id' => '223098091',
            'address' => 'Rua das Flores 125',
            'postalcode' => '4100-100',
            'city' => 'Porto',
            'country' => 'ES',
            'phone' => '222333444',
            'email' => 'alberto.albertino@aa.pt',
            'notes' => 'Client Notes',
            'default_pay_due' => '30',
            'status' => 'inactive',
        ])->and($payload->has('id'))->toBeFalse(),
    };
})->with('providers');

it('does not send the fields the client does not set', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::update(ClientData::from([
        'id' => '99999',
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    expect($payload->has('country_id'))->toBeFalse()
        ->and($payload->has('language_id'))->toBeFalse()
        ->and($payload->has('maturity_date_id'))->toBeFalse()
        ->and($payload->has('discount'))->toBeFalse()
        ->and($payload->has('credit_limit'))->toBeFalse()
        ->and($payload->has('delivery_method_id'))->toBeFalse()
        ->and($payload->has('payment_method_id'))->toBeFalse()
        ->and($payload->has('payment_day'))->toBeFalse()
        ->and($payload->has('salesman_id'))->toBeFalse();
})->with('providers');

it('does not send the moloni number when the client has no reference', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::update(ClientData::from([
        'id' => '99999',
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    expect($payload->has('number'))->toBeFalse()
        ->and($payload->has('external_reference'))->toBeFalse();
})->with('providers')->note('todo: review this test. why are sending for both providers and specifying moloni in the name?');
