<?php

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\FindClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('is an instance of the correct when creating a client', function (Provider $provider) {
    expect(Client::create(ClientData::from([])))->toBeInstanceOf(CreateClient::class);
})->with('providers');

it('is an instance of the correct when finding a client', function (Provider $provider) {
    expect(Client::find())->toBeInstanceOf(FindClient::class);
})->with('providers');

it('is an instance of the correct when getting a client', function (Provider $provider) {
    expect(Client::get(ClientData::from([])))->toBeInstanceOf(GetClient::class);
})->with('providers');
