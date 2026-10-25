<?php

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;

test('create client request is successful', function (Provider $provider, string $createFixture, $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['name' => 'Quim', 'vat' => 123456789, 'address' => 'Rua das Flores 125', 'city' => 'Porto']);
    $data = Client::create($client)->execute()->getClient();

    expect($data->id)->toBeString()
        ->and($data->name)->toBeString()
        ->and($data->vat)->toBeInt();

    match ($provider) {
        Provider::MOLONI => Http::assertSentCount(3),
        default => Http::assertSentCount(1)
    };
})->with('providers', ['create'], ['response_simple']);

test('unsupported properties are kept in additional data', function (Provider $provider, string $responseFixture) {
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

test('client create returns the id as a string and the default pay due as an int', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['name' => 'Quim', 'vat' => 123456789, 'address' => 'Rua das Flores 125', 'city' => 'Porto']);
    $data = Client::create($client)->execute()->getClient();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($data->id)->toBe('99999')
            ->and($data->defaultPayDue)->toBe(15),
        Provider::MOLONI => expect($data->id)->toBeString()
            ->and($data->defaultPayDue)->toBeInstanceOf(Optional::class),
    };
})->with([
    'vendus' => [fn () => cegidVendusProvider(), 'response'],
    'moloni' => [fn () => moloniProvider(), 'response_simple'],
]);
