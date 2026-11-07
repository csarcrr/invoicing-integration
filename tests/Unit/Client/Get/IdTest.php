<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('can set the client id', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->client()->files('response')));
    $client = ClientData::from(['id' => '999999']);

    Client::get($client)->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => Str::contains($request->url(), '999999'),
        Provider::MOLONI => Str::contains($request->body(), 'customer_id=999999'),
    });
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    $client = ClientData::from([]);

    Client::get($client)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Client ID is required.');

it('fails when the provider has no client with the id', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404),
        Provider::MOLONI => mockResponse([]),
    });

    Client::get(ClientData::from(['id' => '999999']))->execute();
})->with('providers')->throws(RequestFailedException::class);
