<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

it('sends the client default pay due', function (Provider $provider) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));
    }

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'defaultPayDue' => DueDateTerm::Days30,
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('maturity_date_id'))->toBe(4322)
            ->and($payload->has('payment_day'))->toBeFalse(),
        Provider::CEGID_VENDUS => expect($payload->get('default_pay_due'))->toBe('30'),
    };
})->with('providers');

it('sends the 15 day term as the moloni default pay due when the client has none', function (Provider $provider) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse([
            ['maturity_date_id' => 4321, 'name' => 'Pronto Pagamento', 'days' => 0],
            ['maturity_date_id' => 4323, 'name' => '15 dias', 'days' => 15],
            ['maturity_date_id' => 4322, 'name' => '30 dias', 'days' => 30],
        ]));
    }

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('maturity_date_id'))->toBe(4323)
            ->and($payload->has('payment_day'))->toBeFalse(),
        Provider::CEGID_VENDUS => expect($payload->has('default_pay_due'))->toBeFalse(),
    };
})->with('providers');

it('sends a zero day default pay due as now on cegid vendus and as the closest due date on moloni', function (Provider $provider) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));
    }

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'defaultPayDue' => DueDateTerm::Days0,
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('maturity_date_id'))->toBe(4321),
        Provider::CEGID_VENDUS => expect($payload->get('default_pay_due'))->toBe('now'),
    };
})->with('providers');

it('rejects a default pay due outside the due date terms', function () {
    ClientData::make(['defaultPayDue' => 20]);
})->throws(ValidationException::class);
