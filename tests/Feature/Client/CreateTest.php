<?php

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use Illuminate\Support\Facades\Http;

test('create client request is sent', function (CreateClient $client, Fixtures $fixture, IntegrationProvider $provider, string $createFixture, $responseFixture) {
    Http::fake(
        mockResponse($provider, $fixture->response()->client()->files($responseFixture))
    );

    $client->execute();

    Http::assertSentCount(1);
})->with('client-full', 'providers', ['create'], ['response']);

test('handles errors successfully', function (CreateClient $client, Fixtures $fixture, IntegrationProvider $provider) {
    Http::fake(
        mockResponse($provider, [], 500)
    );

    $client->execute();
})->with('client-full', 'providers')->throws(FailedReachingProviderException::class);
