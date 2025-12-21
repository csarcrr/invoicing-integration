<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
});

it('sets and validates client city correctly', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->client->setVat('123456789');
    $this->client->setName('Client Name');
    $this->client->setCity('Porto');

    $this->invoice->addItem($this->item);
    $this->invoice->setClient($this->client);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('client')['city'])->toBe('Porto');
});
