<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
});

it('has a valid final price payload', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addItem($this->item);
    $this->invoice->setType(DocumentType::Invoice);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('items')->first()['gross_price'])
        ->toBe(5.0);
});
