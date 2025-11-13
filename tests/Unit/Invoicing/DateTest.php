<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

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
