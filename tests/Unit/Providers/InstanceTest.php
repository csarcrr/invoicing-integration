<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('returns an instance of CreateInvoice', function (Provider $provider) {
    config()->set('invoicing-integration.provider', $provider->value);

    expect(Invoice::create())->toBeInstanceOf(ShouldCreateInvoice::class);
})->with('providers');

it('returns an instance of CreateClient', function (Provider $provider) {
    config()->set('invoicing-integration.provider', $provider->value);

    $client = ClientData::from(['name' => 'Alberto Albertino']);

    expect(Client::create($client))->toBeInstanceOf(CreateClient::class);
})->with('providers');
