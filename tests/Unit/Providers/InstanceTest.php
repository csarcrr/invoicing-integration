<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Client;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ClientData;
use CsarCrr\InvoicingIntegration\Invoice;

it('returns an instance of CreateInvoice', function (Provider $provider) {
    config()->set('invoicing-integration.provider', $provider->value);

    expect(Invoice::create())->toBeInstanceOf(CreateInvoice::class);
})->with('providers');

it('returns an instance of CreateClient', function (Provider $provider) {
    config()->set('invoicing-integration.provider', $provider->value);

    $client = ClientData::name('Alberto Albertino');

    expect(Client::create($client))->toBeInstanceOf(CreateClient::class);
})->with('providers');
