<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\InvoiceItem;

beforeEach(function () {
    config()->set('invoicing-integration.provider', 'vendus');
    config()->set('invoicing-integration.providers.vendus.key', '1234');
    config()->set('invoicing-integration.providers.vendus.mode', 'test');
    config()->set('invoicing-integration.providers.vendus.config.payments', [
        DocumentPaymentMethod::MB->value => 19999,
        DocumentPaymentMethod::CREDIT_CARD->value => 29999,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => 39999,
        DocumentPaymentMethod::MONEY->value => 49999,
        DocumentPaymentMethod::MONEY_TRANSFER->value => 59999,
    ]);
});

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
