<?php

use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

it('assigns a client to an invoice', function () {
    $invoice = Invoice::create();
    $invoice->setClient(new InvoiceClient(vat: '123456789'));

    expect($invoice->client()->vat)->toBe('123456789');
});

it('fails to invoice when client has name but no vat', function () {
    $invoice = Invoice::create();
    $invoice->setClient(new InvoiceClient(name: 'John Doe'));
    $invoice->addItem(new InvoiceItem('reference-1'));

    $invoice->invoice();
})->throws(InvoiceRequiresVatWhenClientHasName::class);

it('fails to invoice when vat is not valid', function () {
    $invoice = Invoice::create();
    $invoice->setClient(new InvoiceClient(vat: ''));
    $invoice->addItem(new InvoiceItem('reference-1'));

    $invoice->invoice();
})->throws(InvoiceRequiresClientVatException::class);
