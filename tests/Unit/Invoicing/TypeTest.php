<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('can assign all different invoice types', function ($type) {
    $type = DocumentType::from($type);
    $this->invoice->setType($type);

    expect($this->invoice->type())->toBe($type);
})->with(DocumentType::options()); // when using CASES it causes clutter in the test results
