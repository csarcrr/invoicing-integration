<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
});

it('has a valid type', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addItem($this->item);
    $this->invoice->setType(InvoiceType::Invoice);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('type'))->toBe('FT');
});
