<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('can set a related document', function () {
    $invoice = Invoice::create();

    $invoice->setType(DocumentType::CreditNote);
    $invoice->addRelatedDocument('FT 01P2025/1');

    expect($invoice->relatedDocuments())->toContain('FT 01P2025/1');
});
