<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

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

it('automatically defines a date when no date is provided', function () {
    $invoice = Invoice::create();

    expect($invoice->date())->toBeInstanceOf(Carbon::class);
});

it('can change the date', function () {
    $invoice = Invoice::create();
    $invoice->setDate(Carbon::now()->addDays(5));

    expect($invoice->date())->toBeInstanceOf(Carbon::class);
    expect($invoice->date()->toDateString())->toBe(Carbon::now()->addDays(5)->toDateString());
});
