<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

it('has a valid client payload', function () {
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
