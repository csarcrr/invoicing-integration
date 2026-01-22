<?php

use CsarCrr\InvoicingIntegration\ClientAction;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Facades\ClientData;
use Illuminate\Support\Facades\Http;

test('create client request is successful', function (Provider $provider, string $createFixture, $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::name('Quim');
    $data = ClientAction::create($client)->execute();

    expect($data->getId())->not->toBeNull();
    Http::assertSentCount(1);
})->with('providers', ['create'], ['response']);

test('handles errors successfully', function (Provider $provider) {
    Http::fake(mockResponse([], 500));

    $client = ClientData::name('Quim');
    ClientAction::create($client)->execute();
})->with('providers')->throws(FailedReachingProviderException::class);
