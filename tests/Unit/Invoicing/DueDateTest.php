<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\DueDate\DueDateCannotBeInPastException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('can set a due date', function () {
    $dueDate = Carbon::now();

    $this->invoice->setDueDate($dueDate);

    expect($this->invoice->dueDate())->toEqual($dueDate);
});

it('fails when date is past', function () {
    $dueDate = Carbon::now()->subDay();

    $this->invoice->setDueDate($dueDate);

    expect($this->invoice->dueDate())->toEqual($dueDate);
})->throws(DueDateCannotBeInPastException::class);
