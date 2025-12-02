<?php

use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

it('assigns a client to an invoice', function () {
    $invoice = Invoice::create();
    $invoice->setClient(new InvoiceClient(vat: '123456789'));

    expect($invoice->client()->vat)->toBe('123456789');
});

it('sets all client properties', function () {
    $client = new InvoiceClient;
    $client->setVat('123456789');
    $client->setName('Joao Alberto');
    $client->setAddress('Rua das Flores 123');
    $client->setCity('Porto');
    $client->setPostalCode('0000-000');
    $client->setCountry('PT');
    $client->setEmail('email@mail.com');
    $client->setPhone('123456789');

    expect($client->vat())->toBe('123456789');
    expect($client->name())->toBe('Joao Alberto');
    expect($client->address())->toBe('Rua das Flores 123');
    expect($client->city())->toBe('Porto');
    expect($client->postalCode())->toBe('0000-000');
    expect($client->country())->toBe('PT');
    expect($client->email())->toBe('email@mail.com');
    expect($client->phone())->toBe('123456789');
});

it('fails to invoice when client has name but no vat', function () {
    $invoice = Invoice::create();
    $invoice->setClient(new InvoiceClient(name: 'John Doe'));
    $invoice->addItem(new InvoiceItem('reference-1'));

    $invoice->invoice();
})->throws(InvoiceRequiresVatWhenClientHasName::class);

it('fails to invoice when vat is not valid', function () {
    $invoice = Invoice::create();
    $invoice->setClient(new InvoiceClient(vat: ''));
    $invoice->addItem(new InvoiceItem('reference-1'));

    $invoice->invoice();
})->throws(InvoiceRequiresClientVatException::class);
