<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;

it('assigns a client', function (CreateInvoice $invoice) {
    $invoice->client(
        new Client(
            name: 'John Doe',
            vat: '123456789'
        )
    );

    expect($invoice->getClient())->toBeInstanceOf(Client::class);
    expect($invoice->getClient()->getName())->toBe('John Doe');
})->with('create-invoice');

it('has the expected payload', function (
    CreateInvoice $invoice,
    IntegrationProvider $provider,
    string $fixture
) {
    $data = Fixtures::request($provider)->invoice()->client()->files($fixture);

    $client = new Client(
        name: 'John Doe',
        vat: '123456789'
    );

    $invoice->client($client);

    expect($invoice->payload())->toMatchArray($data);
})->with('create-invoice', 'providers', ['simple_client']);

it('fails when vat is not valid', function (
    CreateInvoice $invoice,
    IntegrationProvider $provider
) {

    $client = new Client(
        vat: ''
    );

    $invoice->client($client);
})->with('create-invoice', 'providers')->throws(InvoiceRequiresClientVatException::class);

it('fails when name is provided but vat is missing', function (
    CreateInvoice $invoice, IntegrationProvider $provider
) {
    if($provider !== IntegrationProvider::CEGID_VENDUS) {
        $this->markTestSkipped('This test is only for CegidVendus provider.');
    }

    $client = new Client(
        name: 'John Doe',
    );

    $invoice->client($client);
})->with('create-invoice', 'providers')->throws(InvoiceRequiresVatWhenClientHasName::class);
