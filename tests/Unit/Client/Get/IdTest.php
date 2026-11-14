<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

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
