<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoicePayment;

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

it('assigns a payment', function () {
    $invoice = Invoice::create();
    $invoice->addPayment(new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500));

    expect($invoice->payments()->count())->toBe(1);
    expect($invoice->payments()->first())->toBeInstanceOf(InvoicePayment::class);
    expect($invoice->payments()->first()->method())->toBe(DocumentPaymentMethod::CREDIT_CARD);
    expect($invoice->payments()->first()->amount())->toBe(500);
});
