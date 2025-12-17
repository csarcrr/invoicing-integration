<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoicePayment;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('assigns a payment', function () {
    $payment = new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500);

    $this->invoice->addPayment($payment);

    expect($this->invoice->payments()->count())->toBe(1);
    expect($this->invoice->payments()->first())->toBeInstanceOf(InvoicePayment::class);
    expect($this->invoice->payments()->first()->method())->toBe(DocumentPaymentMethod::CREDIT_CARD);
    expect($this->invoice->payments()->first()->amount())->toBe(500);
});

it('accumulates payments of same type', function () {
    $payment1 = new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500);
    $payment2 = new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500);
    $payment3 = new InvoicePayment(DocumentPaymentMethod::CREDIT_CARD, amount: 500);

    $this->invoice->addPayment($payment1);
    $this->invoice->addPayment($payment2);
    $this->invoice->addPayment($payment3);

    $paymentsSum = $this->invoice
        ->payments()
        ->sum(fn (InvoicePayment $payment) => $payment->amount());

    expect($this->invoice->payments()->count())->toBe(3);
    expect($paymentsSum)->toBe(1500);
});
