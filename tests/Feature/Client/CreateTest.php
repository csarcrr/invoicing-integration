<?php

use CsarCrr\InvoicingIntegration\Client;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Facades\Http;

test('create client request is successful', function (IntegrationProvider $provider, Fixtures $fixture, string $createFixture, $responseFixture) {
    Http::fake(
        mockResponse($provider, $fixture->response()->client()->files($responseFixture))
    );

    $client = (new ClientData)->name('Quim');
    $data = Client::create($client)->execute();

    expect($data->getId())->not->toBeNull();
    Http::assertSentCount(1);
})->with('client-full', ['create'], ['response']);

test('handles errors successfully', function (IntegrationProvider $provider, Fixtures $fixture) {
    Http::fake(
        mockResponse($provider, [], 500)
    );

    $client = (new ClientData)->name('Quim');
    Client::create($client)->execute();
})->with('client-full')->throws(FailedReachingProviderException::class);
