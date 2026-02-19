<?php

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('is an instance of the correct class when creating an invoice', function (Provider $provider) {
    expect(Invoice::create(InvoiceData::make([])))->toBeInstanceOf(ShouldCreateInvoice::class);
})->with('providers');
