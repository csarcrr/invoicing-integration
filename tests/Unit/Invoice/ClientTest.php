<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('has the simple client payload', function (CreateInvoice $invoice, Fixtures $fixture) {
    $invoice->client(
        new Client(
            name: 'John Doe',
            vat: '123456789'
        )
    );

    expect($invoice->getClient())->toBeInstanceOf(Client::class);
    expect($invoice->getClient()->getName())->toBe('John Doe');
})->with('create-invoice');

it('has the correct full client payload', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->client()->files($fixtureName);

    $client = new Client(
        name: 'John Doe',
        vat: '123456789'
    );

    $client->address('Rua das Flores 125');
    $client->city('Porto');
    $client->postalCode('4410-000');
    $client->country('PT');
    $client->email('john.doe@mail.com');
    $client->phone('220123123');
    $client->irsRetention(true);

    $item = new Item(reference: 'reference-1');

    $invoice->client($client);
    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['complete_client']);

it('fails when vat is not valid', function (CreateInvoice $invoice, Fixtures $fixture) {
    $client = new Client(
        vat: ''
    );

    $item = new Item(
        reference: 'reference-1'
    );

    $invoice->item($item);
    $invoice->client($client);

    $invoice->getPayload();
})->with('create-invoice')->throws(InvoiceRequiresClientVatException::class);

it('fails when name is provided but vat is missing', function (
    CreateInvoice $invoice, Fixtures $fixture, IntegrationProvider $provider
) {
    if ($provider !== IntegrationProvider::CEGID_VENDUS) {
        $this->markTestSkipped('This test is only for CegidVendus provider.');
    }

    $client = new Client(
        name: 'John Doe',
    );

    $item = new Item(
        reference: 'reference-1'
    );

    $invoice->item($item);
    $invoice->client($client);

    $invoice->getPayload();
})->with('create-invoice', 'providers')->throws(InvoiceRequiresVatWhenClientHasName::class);
