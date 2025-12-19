<?php

use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem;
    $this->client = new InvoiceClient;
});

it('has the correct tax applied', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::REDUCED);

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['tax_id'])->toBe('RED');
});
