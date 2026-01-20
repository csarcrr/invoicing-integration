<?php

use CsarCrr\InvoicingIntegration\Client;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ClientData;
use Illuminate\Support\Facades\Http;

test('a client get request is successful', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::id(999999);
    $data = Client::get($client)->execute();

    expect($data->getName())->not->toBeNull()
        ->and($data->getEmail())->not->toBeNull()
        ->and($data->getIrsRetention())->toBeTrue();

    Http::assertSentCount(1);
})->with('providers', ['response']);
