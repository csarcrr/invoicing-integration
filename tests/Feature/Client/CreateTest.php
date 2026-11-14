<?php

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;

test('create client request is successful', function (Provider $provider, string $createFixture, $responseFixture) {
    fakeProviderDueDates($provider);

    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['name' => 'Quim', 'vat' => 123456789, 'address' => 'Rua das Flores 125', 'city' => 'Porto']);
    $data = Client::create($client)->execute()->getClient();

    expect($data->id)->toBeString()
        ->and($data->name)->toBeString()
        ->and($data->vat)->toBeInt();

    Http::assertSentCount(1);
})->with('providers', ['create'], ['response_simple']);

test('unsupported properties are kept in additional data', function (Provider $provider, string $responseFixture) {
    fakeProviderDueDates($provider);

    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['name' => 'Quim', 'vat' => 123456789, 'address' => 'Rua das Flores 125', 'city' => 'Porto']);
    $data = Client::create($client)->execute()->getClient();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($data->getAdditionalData())->toHaveKey('balance'),
        Provider::MOLONI => expect($data->getAdditionalData())->toHaveKey('valid'),
    };
})->with([
    'vendus' => [fn () => cegidVendusProvider(), 'response'],
    'moloni' => [fn () => moloniProvider(), 'response_simple'],
]);

test('client create returns the id as a string and the default pay due as a due date term', function (Provider $provider, string $responseFixture) {
    fakeProviderDueDates($provider);

    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['name' => 'Quim', 'vat' => 123456789, 'address' => 'Rua das Flores 125', 'city' => 'Porto']);
    $data = Client::create($client)->execute()->getClient();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($data->id)->toBe('99999')
            ->and($data->defaultPayDue)->toBe(DueDateTerm::Days15),
        Provider::MOLONI => expect($data->id)->toBe('99999')
            ->and($data->defaultPayDue)->toBeInstanceOf(Optional::class),
    };
})->with([
    'vendus' => [fn () => cegidVendusProvider(), 'response'],
    'moloni' => [fn () => moloniProvider(), 'response_simple'],
]);

test('fails when the provider rejects the client', function (Provider $provider) {
    fakeProviderDueDates($provider);

    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse([
            'errors' => [
                ['code' => 'A001', 'message' => 'NIF em duplicado, já existe um registo associado ao NIF indicado'],
            ],
        ], 400),
        Provider::MOLONI => mockResponse([
            ['code' => '4 vat', 'description' => "Field 'vat' must be unique"],
        ]),
    });

    expect(fn () => Client::create(ClientData::from([
        'name' => 'Quim',
        'vat' => 123456789,
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->execute())->toThrow(RequestFailedException::class, match ($provider) {
        Provider::CEGID_VENDUS => 'A001 - NIF em duplicado, já existe um registo associado ao NIF indicado',
        Provider::MOLONI => "4 vat - Field 'vat' must be unique",
    });
})->with('providers');
