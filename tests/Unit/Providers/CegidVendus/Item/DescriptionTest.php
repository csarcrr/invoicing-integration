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

it('has a description', function () {
    $this->item->setReference('reference-1');
    $this->item->setNote('Test Item note');

    $this->invoice->addItem($this->item);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('items')->first()['text'])->toBe('Test Item note');
});
