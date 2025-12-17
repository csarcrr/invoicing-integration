<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem;
    $this->client = new InvoiceClient;
});

it('has a valid simple client payload', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->client->setVat('123456789');
    $this->client->setName('Client Name');

    $this->invoice->addItem($this->item);
    $this->invoice->setClient($this->client);
    $this->invoice->setType(DocumentType::Invoice);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('client')['fiscal_id'])->toBe('123456789');
    expect($resolve->payload()->get('client')['name'])->toBe('Client Name');
});

it('has a valid full client payload', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->client->setVat('123456789');
    $this->client->setName('Client Name');
    $this->client->setAddress('Rua das Flores 123');
    $this->client->setCity('Porto');
    $this->client->setPostalCode('0000-000');
    $this->client->setCountry('PT');
    $this->client->setEmail('email@mail.com');
    $this->client->setPhone('123456789');

    $this->invoice->addItem($this->item);
    $this->invoice->setClient($this->client);
    $this->invoice->setType(DocumentType::Invoice);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('client')['fiscal_id'])->toBe('123456789');
    expect($resolve->payload()->get('client')['name'])->toBe('Client Name');
    expect($resolve->payload()->get('client')['address'])->toBe('Rua das Flores 123');
    expect($resolve->payload()->get('client')['city'])->toBe('Porto');
    expect($resolve->payload()->get('client')['postalcode'])->toBe('0000-000');
    expect($resolve->payload()->get('client')['country'])->toBe('PT');
    expect($resolve->payload()->get('client')['email'])->toBe('email@mail.com');
    expect($resolve->payload()->get('client')['phone'])->toBe('123456789');
});
