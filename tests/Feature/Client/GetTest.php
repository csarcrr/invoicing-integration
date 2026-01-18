<?php

use CsarCrr\InvoicingIntegration\Client;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Facades\Http;

test('a client get request is successful', function (IntegrationProvider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = (new ClientData())->id(999999);
    $data = Client::get($client)->execute();

    expect($data->getName())->not->toBeNull()
        ->and($data->getEmail())->not->toBeNull();

    Http::assertSentCount(1);
})->with('providers', ['response']);
