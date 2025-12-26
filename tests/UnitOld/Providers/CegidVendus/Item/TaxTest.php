<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
});

it('has the correct tax applied', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::REDUCED);

    $this->invoice->addItem($this->item);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('items')->first()['tax_id'])->toBe('RED');
});
