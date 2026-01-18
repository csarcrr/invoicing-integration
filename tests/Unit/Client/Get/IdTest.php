<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Client;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('can set the client id', function (IntegrationProvider $provider) {
    Http::fake(mockResponse([], 200));
    $client = new ClientData();
    $client->id(999999);

    Client::get($client)->execute();

    Http::assertSent(function (Request $request) {
        return Str::contains($request->url(), '999999');
    });
})->with('providers');
