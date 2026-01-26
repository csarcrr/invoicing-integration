<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('can set the client id', function (Provider $provider) {
    Http::fake(mockResponse([], 200));
    $client = ClientData::id(999999);

    Client::get($client)->execute();

    Http::assertSent(function (Request $request) {
        return Str::contains($request->url(), '999999');
    });
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    $client = ClientData::getFacadeRoot();

    Client::get($client)->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Client ID is required.');
