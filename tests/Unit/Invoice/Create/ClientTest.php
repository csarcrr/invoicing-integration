<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('has the simple client payload', function (IntegrationProvider $provider) {
    $invoice = Invoice::create();

    $client = new ClientData;
    $client->name('John Doe');
    $client->vat('123456789');

    $invoice->client($client);

    expect($invoice->getClient())->toBeInstanceOf(ClientData::class)
        ->and($invoice->getClient()->getName())->toBe('John Doe');
})->with('providers');

it('has the correct full client payload', function (IntegrationProvider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->client()->files($fixtureName);

    $invoice = Invoice::create();

    $client = new ClientData;
    $client->name('John Doe')->vat('123456789');

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
})->with('providers', ['complete_client']);

it('fails when vat is not valid', function (IntegrationProvider $provider) {
    $invoice = Invoice::create();

    $client = (new ClientData)->vat('');

    $item = new Item(
        reference: 'reference-1'
    );

    $invoice->item($item);
    $invoice->client($client);

    $invoice->getPayload();
})->with('providers')->throws(InvoiceRequiresClientVatException::class);

it('fails when name is provided but vat is missing', function (IntegrationProvider $provider) {
    if ($provider !== IntegrationProvider::CEGID_VENDUS) {
        $this->markTestSkipped('This test is only for CegidVendus provider.');
    }

    $invoice = Invoice::create();

    $client = (new ClientData)->name('John Doe');

    $item = new Item(
        reference: 'reference-1'
    );

    $invoice->item($item);
    $invoice->client($client);

    $invoice->getPayload();
})->with('providers')->throws(InvoiceRequiresVatWhenClientHasName::class);

it('fails when assigning an invalid country', function () {
    $client = new ClientData;

    $client->country('InvalidCountry');
})->throws(InvalidCountryException::class);
