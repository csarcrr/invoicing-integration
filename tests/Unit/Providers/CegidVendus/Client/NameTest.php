<?php

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem;
    $this->client = new InvoiceClient;
});

it('sets and validates client name correctly', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->client->setVat('123456789');
    $this->client->setName('Client Name');

    $this->invoice->addItem($this->item);
    $this->invoice->setClient($this->client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('client')['name'])->toBe('Client Name');
});
