<?php

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Facades\Http;

test('create client request is successful', function (Provider $provider, string $createFixture, $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['name' => 'Quim', 'vat' => 123456789]);
    $data = Client::create($client)->execute();

    expect($data->id)->toBeInt()->and($data->name)->toBeString()->and($data->vat)->toBeInt();

    Http::assertSentCount(1);
})->with('providers', ['create'], ['response_simple']);

test('handles errors successfully', function (Provider $provider) {
    Http::fake(mockResponse([], 500));

    $client = ClientData::from(['name' => 'Quim']);
    Client::create($client)->execute();
})->with('providers')->throws(FailedReachingProviderException::class);
