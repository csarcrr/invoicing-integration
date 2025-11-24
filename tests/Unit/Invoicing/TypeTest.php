<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('can assign all different invoice types', function ($type) {
    $type = DocumentType::from($type);
    $invoice = Invoice::create();
    $invoice->setType($type);

    expect($invoice->type())->toBe($type);
})->with(DocumentType::options()); // when using CASES it causes clutter in the test results
