<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('can set credit note reason', function () {
    $invoice = Invoice::create();

    $invoice->setType(DocumentType::CreditNote);
    $invoice->setCreditNoteReason('Product returned by customer');
})->todo();

// it('can set what to do with the stock', function () {})->todo();

// it('can set a related document', function () {})->todo();
