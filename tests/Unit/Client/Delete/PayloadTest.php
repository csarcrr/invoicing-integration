<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('sends the delete request for the client id', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse(''),
        Provider::MOLONI => mockResponse(fixtures()->response()->client()->files('delete')),
    });

    Client::delete(ClientData::from(['id' => '99999']))->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => $request->method() === 'DELETE'
            && Str::endsWith($request->url(), 'clients/99999'),
        Provider::MOLONI => Str::contains($request->url(), 'customers/delete')
            && Str::contains($request->body(), 'customer_id=99999')
            && Str::contains($request->body(), 'company_id=123456'),
    });
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    Client::delete(ClientData::from([]))->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Client ID is required.');
