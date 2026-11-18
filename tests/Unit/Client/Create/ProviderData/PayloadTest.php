<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;

it('transforms to provider payload with all client fields', function (Provider $provider) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));
    }

    $data = fixtures()->request()->client()->files('client_full');

    $client = ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'postalCode' => '4100-100',
        'city' => 'Porto',
        'phone' => '222333444',
        'notes' => 'Client Notes',
        'email' => 'alberto.albertino@aa.pt',
        'country' => 'PT',
        'emailNotification' => true,
        'defaultPayDue' => DueDateTerm::Days15,
        'irsRetention' => true,
    ]);

    $create = Client::create($client);

    expect($create->getPayload()->toArray())->toMatchArray($data);
})->with('providers');

it('sends only the client fields that are set', function (Provider $provider) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));
    }

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload()->toArray();

    expect($payload)->each->not->toBeInstanceOf(Optional::class)
        ->and($payload)->not->toHaveKey('irs_retention')
        ->and($payload)->not->toHaveKey('send_email');
})->with('providers');
