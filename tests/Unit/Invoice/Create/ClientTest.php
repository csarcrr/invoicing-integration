<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('has the simple client payload', function (Provider $provider) {
    $invoice = Invoice::create();

    $client = ClientData::name('John Doe')->vat('123456789');

    $invoice->client($client);

    expect($invoice->getClient())->toBeInstanceOf(ClientData::class)
        ->and($invoice->getClient()->getName())->toBe('John Doe');
})->with('providers');

it('has the correct full client payload', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->client()->files($fixtureName);

    $invoice = Invoice::create();

    $client = ClientData::from([
        'name'         => 'John Doe',
        'vat'          => '123456789',
        'address'      => 'Rua das Flores 125',
        'city'         => 'Porto',
        'postalCode'   => '4410-000',
        'country'      => 'PT',
        'email'        => 'john.doe@mail.com',
        'phone'        => '220123123',
        'irsRetention' => true,
    ]);

    $item = new Item(reference: 'reference-1');

    $invoice->client($client);
    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['complete_client']);

it('fails when vat is not valid', function (Provider $provider) {
    $invoice = Invoice::create();

    $client = ClientData::vat('');

    $item = new Item(
        reference: 'reference-1'
    );

    $invoice->item($item);
    $invoice->client($client);

    $invoice->getPayload();
})->with('providers')->throws(InvoiceRequiresClientVatException::class);

it('fails when name is provided but vat is missing', function (Provider $provider) {
    if ($provider !== Provider::CEGID_VENDUS) {
        $this->markTestSkipped('This test is only for CegidVendus provider.');
    }

    $invoice = Invoice::create();

    $client = ClientData::name('John Doe');

    $item = new Item(
        reference: 'reference-1'
    );

    $invoice->item($item);
    $invoice->client($client);

    $invoice->getPayload();
})->with('providers')->throws(InvoiceRequiresVatWhenClientHasName::class);

it('fails when assigning an invalid country', function () {
    ClientData::country('InvalidCountry');
})->throws(InvalidCountryException::class);
