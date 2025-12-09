<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

it('has a valid simple client payload', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $client = new InvoiceClient(vat: '123456789', name: 'Client Name');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setClient($client);
    $invoicing->setType(DocumentType::Invoice);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('client')['fiscal_id'])->toBe('123456789');
    expect($resolve->payload()->get('client')['name'])->toBe('Client Name');
});

it('has a valid full client payload', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $client = new InvoiceClient;
    $client->setVat('123456789');
    $client->setName('Client Name');
    $client->setAddress('Rua das Flores 123');
    $client->setCity('Porto');
    $client->setPostalCode('0000-000');
    $client->setCountry('PT');
    $client->setEmail('email@mail.com');
    $client->setPhone('123456789');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setClient($client);
    $invoicing->setType(DocumentType::Invoice);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
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
