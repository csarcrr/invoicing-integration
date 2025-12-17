<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoicePayment;

it('assigns a payment', function () {
    $invoice = Invoice::create();
    $invoice->addPayment(new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500));

    expect($invoice->payments()->count())->toBe(1);
    expect($invoice->payments()->first())->toBeInstanceOf(InvoicePayment::class);
    expect($invoice->payments()->first()->method())->toBe(DocumentPaymentMethod::CREDIT_CARD);
    expect($invoice->payments()->first()->amount())->toBe(500);
});

it('accumulates payments of same type', function () {
    $invoice = Invoice::create();

    $invoice->addPayment(new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500));
    $invoice->addPayment(new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500));
    $invoice->addPayment(new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500));

    expect($invoice->payments()->count())->toBe(3);
    expect($invoice->payments()->sum(fn (InvoicePayment $payment) => $payment->amount()))->toBe(1500);
});
