<?php

use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->client = new InvoiceClient;
    $this->invoice = Invoice::create();
});

it('assigns a client to an invoice', function () {
    $this->client->setVat('123456789');

    $this->invoice->setClient($this->client);

    expect($this->invoice->client()->vat)->toBe('123456789');
});

it('sets and gets client vat', function () {
    $this->client->setVat('123456789');
    expect($this->client->vat())->toBe('123456789');
});

it('sets and gets client name', function () {
    $this->client->setName('Joao Alberto');
    expect($this->client->name())->toBe('Joao Alberto');
});

it('sets and gets client address', function () {
    $this->client->setAddress('Rua das Flores 123');
    expect($this->client->address())->toBe('Rua das Flores 123');
});

it('sets and gets client city', function () {
    $this->client->setCity('Porto');
    expect($this->client->city())->toBe('Porto');
});

it('sets and gets client postal code', function () {
    $this->client->setPostalCode('0000-000');
    expect($this->client->postalCode())->toBe('0000-000');
});

it('sets and gets client country', function () {
    $this->client->setCountry('PT');
    expect($this->client->country())->toBe('PT');
});

it('sets and gets client email', function () {
    $this->client->setEmail('email@mail.com');
    expect($this->client->email())->toBe('email@mail.com');
});

it('sets and gets client phone', function () {
    $this->client->setPhone('123456789');
    expect($this->client->phone())->toBe('123456789');
});

it('fails to invoice when client has name but no vat', function () {
    $this->client->setName('John Doe');
    $this->invoice->setClient($this->client);
    $this->invoice->addItem(new InvoiceItem('reference-1'));

    $this->invoice->invoice();
})->throws(InvoiceRequiresVatWhenClientHasName::class);

it('fails to invoice when vat is not valid', function () {
    $this->client->setVat('');
    $this->invoice = Invoice::create();
    $this->invoice->setClient($this->client);
    $this->invoice->addItem(new InvoiceItem('reference-1'));

    $this->invoice->invoice();
})->throws(InvoiceRequiresClientVatException::class);
