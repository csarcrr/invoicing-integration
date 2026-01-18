<?php

use CsarCrr\InvoicingIntegration\Client;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Facades\Http;

test('create client request is successful', function (IntegrationProvider $provider, string $createFixture, $responseFixture) {
    Http::fake(
        mockResponse(fixtures()->response()->client()->files($responseFixture))
    );

    $client = (new ClientData)->name('Quim');
    $data = Client::create($client)->execute();

    expect($data->getId())->not->toBeNull();
    Http::assertSentCount(1);
})->with('providers', ['create'], ['response']);

test('handles errors successfully', function (IntegrationProvider $provider) {
    Http::fake(
        mockResponse([], 500)
    );

    $client = (new ClientData)->name('Quim');
    Client::create($client)->execute();
})->with('providers')->throws(FailedReachingProviderException::class);
