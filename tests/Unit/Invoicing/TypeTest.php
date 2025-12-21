<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('can assign all different invoice types', function ($type) {
    $type = InvoiceType::from($type);
    $this->invoice->setType($type);

    expect($this->invoice->type())->toBe($type);
})->with(InvoiceType::options()); // when using CASES it causes clutter in the test results
