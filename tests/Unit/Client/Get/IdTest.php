<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Support\Facades\Http;

it('can set the client id', function (Provider $provider) {
    $get = Client::get(ClientData::from(['id' => '999999']));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($get->getUrl())->toBe('/clients/999999')
            ->and($get->getPayload()->toArray())->toBe([]),
        Provider::MOLONI => expect($get->getUrl())->toBe('customers/getOne')
            ->and($get->getPayload()->toArray())->toBe(['customer_id' => '999999']),
    };
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
