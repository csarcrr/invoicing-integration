<?php

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldCreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldFetchClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldFindClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('is an instance of the correct when creating a client', function (Provider $provider) {
    expect(Client::create(ClientData::from([])))->toBeInstanceOf(ShouldCreateClient::class);
})->with('providers');

it('is an instance of the correct when finding a client', function (Provider $provider) {
    expect(Client::find())->toBeInstanceOf(ShouldFindClient::class);
})->with('providers');

it('is an instance of the correct when getting a client', function (Provider $provider) {
    expect(Client::fetch(ClientData::from(['id' => '1'])))->toBeInstanceOf(ShouldFetchClient::class);
})->with('providers');
