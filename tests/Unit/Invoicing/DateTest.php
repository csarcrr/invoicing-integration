<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('automatically defines a date when no date is provided', function () {

    expect($this->invoice->date())->toBeInstanceOf(Carbon::class);
});

it('can change the date', function () {
    $this->invoice->setDate(Carbon::now()->addDays(5));

    expect($this->invoice->date())->toBeInstanceOf(Carbon::class);
    expect($this->invoice->date()->toDateString())->toBe(Carbon::now()->addDays(5)->toDateString());
});
