<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('sends the delete request for the client id', function (Provider $provider) {
    $delete = Client::delete(ClientData::from(['id' => '99999']));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($delete->getUrl())->toBe('clients/99999')
            ->and($delete->getPayload()->toArray())->toBe([]),
        Provider::MOLONI => expect($delete->getUrl())->toBe('customers/delete')
            ->and($delete->getPayload()->toArray())->toBe(['customer_id' => '99999']),
    };
})->with('providers');

it('fails when no id is set', function (Provider $provider) {
    Client::delete(ClientData::from([]))->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Client ID is required.');
