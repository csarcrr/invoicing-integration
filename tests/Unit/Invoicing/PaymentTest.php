<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoicePayment;

it('assigns a payment', function () {
    $payment = new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500);
    
    $invoice = Invoice::create();
    $invoice->addPayment($payment);

    expect($invoice->payments()->count())->toBe(1);
    expect($invoice->payments()->first())->toBeInstanceOf(InvoicePayment::class);
    expect($invoice->payments()->first()->method())->toBe(DocumentPaymentMethod::CREDIT_CARD);
    expect($invoice->payments()->first()->amount())->toBe(500);
});

it('accumulates payments of same type', function () {
    $invoice = Invoice::create();

    $payment1 = new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500);
    $payment2 = new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500);
    $payment3 = new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500);

    $invoice->addPayment($payment1);
    $invoice->addPayment($payment2);
    $invoice->addPayment($payment3);

    $paymentsSum = $invoice
        ->payments()
        ->sum(fn (InvoicePayment $payment) => $payment->amount());

    expect($invoice->payments()->count())->toBe(3);
    expect($paymentsSum)->toBe(1500);
});
