<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote\CreditNoteReasonCannotBeSetException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('can set credit note reason', function () {
    $invoice = Invoice::create();

    $invoice->setType(DocumentType::CreditNote);
    $invoice->setCreditNoteReason('Product returned by customer');

    expect($invoice->creditNoteReason())->toBe('Product returned by customer');
});

it('fails to set credit note reason if document is not credit note', function () {
    $invoice = Invoice::create();

    $invoice->setType(DocumentType::Invoice);
    $invoice->setCreditNoteReason('Product returned by customer');
})->throws(CreditNoteReasonCannotBeSetException::class);

it('can set a related document', function () {
    $invoice = Invoice::create();

    $invoice->setType(DocumentType::CreditNote);
    $invoice->addRelatedDocument('FT 01P2025/1');

    expect($invoice->relatedDocuments())->toContain('FT 01P2025/1');
});

// it('can set what to do with the stock', function () {})->todo();
