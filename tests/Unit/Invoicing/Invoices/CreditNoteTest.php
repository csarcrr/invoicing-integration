<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote\CreditNoteReasonCannotBeSetException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('can set credit note reason', function () {
    $this->invoice->setType(DocumentType::CreditNote);
    $this->invoice->setCreditNoteReason('Product returned by customer');

    expect($this->invoice->creditNoteReason())->toBe('Product returned by customer');
});

it('fails to set credit note reason if document is not credit note', function () {
    $this->invoice->setType(DocumentType::Invoice);
    $this->invoice->setCreditNoteReason('Product returned by customer');
})->throws(CreditNoteReasonCannotBeSetException::class);

it('can set a related document', function () {
    $this->invoice->setType(DocumentType::CreditNote);
    $this->invoice->addRelatedDocument('FT 01P2025/1');

    expect($this->invoice->relatedDocuments())->toContain('FT 01P2025/1');
});

// it('can set what to do with the stock', function () {})->todo();
