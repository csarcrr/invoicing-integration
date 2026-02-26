<?php

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Support\Facades\Http;

test('a client get request is successful', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['id' => 999999]);

    $data = Client::get($client)->execute()->getClient();

    expect($data->name)->toBeString()
        ->and($data->email)->toBeString()
        ->and($data->irsRetention)->toBeTrue();

    Http::assertSentCount(1);
})->with('providers', ['response']);

test('supported properties are not filled in additional data', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['id' => 999999]);

    $data = Client::get($client)->execute()->getClient();

    expect($data->getAdditionalData())
        ->not->toHaveKey('postalCode')
        ->not->toHaveKey('postal_code');
})->with('providers', ['response']);
