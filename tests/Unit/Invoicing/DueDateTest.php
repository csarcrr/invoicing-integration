<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\DueDate\DueDateCannotBeInPastException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('can set a due date', function () {
    $invoice = Invoice::create();

    $dueDate = Carbon::now();

    $invoice->setDueDate($dueDate);

    expect($invoice->dueDate())->toEqual($dueDate);
});

it('fails when date is past', function () {
    $invoice = Invoice::create();

    $dueDate = Carbon::now()->subDay();

    $invoice->setDueDate($dueDate);

    expect($invoice->dueDate())->toEqual($dueDate);
})->throws(DueDateCannotBeInPastException::class);
