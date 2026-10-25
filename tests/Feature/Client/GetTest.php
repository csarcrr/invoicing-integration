<?php

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;

test('a client get request is successful', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['id' => 999999]);

    $data = Client::get($client)->execute()->getClient();

    expect($data->name)->toBeString()
        ->and($data->email)->toBeString()
        ->and($data->country)->toBe('PT');

    match ($provider) {
        Provider::CEGID_VENDUS => expect($data->irsRetention)->toBeTrue(),
        Provider::MOLONI => expect($data->irsRetention)->toBeInstanceOf(Optional::class),
    };

    Http::assertSentCount(1);
})->with('providers', ['response']);

test('supported properties are not filled in additional data', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['id' => 999999]);

    $data = Client::get($client)->execute()->getClient();

    expect($data->getAdditionalData())
        ->not->toHaveKey('postalCode')
        ->not->toHaveKey('postal_code')
        ->not->toHaveKey('zip_code');
})->with('providers', ['response']);

test('unsupported properties are kept in additional data', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['id' => 999999]);

    $data = Client::get($client)->execute()->getClient();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($data->getAdditionalData())->toHaveKey('balance'),
        Provider::MOLONI => expect($data->getAdditionalData())->toHaveKey('website'),
    };
})->with('providers', ['response']);

test('client get returns the id as a string and the default pay due as an int', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['id' => 999999]);

    $data = Client::get($client)->execute()->getClient();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($data->id)->toBe('99999')
            ->and($data->defaultPayDue)->toBe(15),
        Provider::MOLONI => expect($data->id)->toBe('999999')
            ->and($data->defaultPayDue)->toBeInstanceOf(Optional::class),
    };
})->with('providers', ['response']);

test('external reference and status are mapped properties', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['id' => 999999]);

    $data = Client::get($client)->execute()->getClient();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($data->externalReference)->toBe('AB892798/19')
            ->and($data->status)->toBe('active')
            ->and($data->getAdditionalData())
            ->not->toHaveKey('external_reference')
            ->not->toHaveKey('status'),
        Provider::MOLONI => expect($data->externalReference)->toBeInstanceOf(Optional::class)
            ->and($data->status)->toBeInstanceOf(Optional::class),
    };
})->with('providers', ['response']);
