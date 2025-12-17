<?php

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('can set a client', function () {
    $client = new InvoiceClient(999999999, 'Client Name');

    $this->invoice->setClient($client);

    expect($this->invoice->client())->toBeInstanceOf(InvoiceClient::class);
});

it('can set an item', function () {
    $item = new InvoiceItem('reference-1');

    $this->invoice->addItem($item);

    expect($this->invoice->items()->first())->toBeInstanceOf(InvoiceItem::class);
    expect($this->invoice->items())->toContain($item);
});

it('can add multiple items', function () {
    $item1 = new InvoiceItem('reference-1');
    $item2 = new InvoiceItem('reference-2');
    $item3 = new InvoiceItem('reference-3');

    $this->invoice->addItem($item1);
    $this->invoice->addItem($item2);
    $this->invoice->addItem($item3);

    expect($this->invoice->items()->first())->toBeInstanceOf(InvoiceItem::class);
    expect($this->invoice->items())->toContain($item1);
    expect($this->invoice->items())->toContain($item2);
    expect($this->invoice->items())->toContain($item3);
});
