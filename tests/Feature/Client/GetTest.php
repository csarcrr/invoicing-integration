<?php

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Facades\Http;

test('a client get request is successful', function (Provider $provider, string $responseFixture) {
    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $client = ClientData::from(['id' => 999999]);
    $data = Client::get($client)->execute()->toArray();

    expect($data['name'])->not->toBeNull()
        ->and($data['email'])->not->toBeNull()
        ->and($data['irs_retention'])->toBeTrue();

    Http::assertSentCount(1);
})->with('providers', ['response']);
