<?php

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Facades\Http;

test('create client request is successful', function (Provider $provider, string $createFixture, $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['name' => 'Quim']);
    $data = Client::create($client)->execute();

    expect($data->id)->not->toBeNull();
    Http::assertSentCount(1);
})->with('providers', ['create'], ['response']);

test('handles errors successfully', function (Provider $provider) {
    Http::fake(mockResponse([], 500));

    $client = ClientData::from(['name'=>'Quim']);
    Client::create($client)->execute();
})->with('providers')->throws(FailedReachingProviderException::class);
