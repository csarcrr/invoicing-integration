<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('can set a client', function () {
    $client = new Client('999999999', 'Client Name');

    $this->invoice->setClient($client);

    expect($this->invoice->client())->toBeInstanceOf(Client::class);
});

it('can set an item', function () {
    $item = new Item('reference-1');

    $this->invoice->addItem($item);

    expect($this->invoice->items()->first())->toBeInstanceOf(Item::class);
    expect($this->invoice->items())->toContain($item);
});

it('can add multiple items', function () {
    $item1 = new Item('reference-1');
    $item2 = new Item('reference-2');
    $item3 = new Item('reference-3');

    $this->invoice->addItem($item1);
    $this->invoice->addItem($item2);
    $this->invoice->addItem($item3);

    expect($this->invoice->items()->first())->toBeInstanceOf(Item::class);
    expect($this->invoice->items())->toContain($item1);
    expect($this->invoice->items())->toContain($item2);
    expect($this->invoice->items())->toContain($item3);
});
