<?php

use CsarCrr\InvoicingIntegration\Facades\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use CsarCrr\InvoicingIntegration\InvoicingItem;

beforeEach(function () {
    config()->set('invoicing-integration.vendus', 'test_key');
    config()->set('invoicing-integration.test_mode', true);
});

it('can invoice', function () {
    $invoice = InvoicingIntegration::create()
        ->forClient(new InvoicingClient(name: 'Client Name', vat: '123456789'))
        ->withItem(new InvoicingItem(reference: 'ref-1'))
        ->invoice();

    expect($invoice)->toBeClass();
});
