<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('can assign related documents to a receipt', function () {
    $invoice = Invoice::create();
    $invoice->setType(DocumentType::Receipt);
    $invoice->addRelatedDocument('FT 1000');

    expect($invoice->relatedDocuments()->count())->toBe(1);
    expect($invoice->relatedDocuments()->first())->toBe('FT 1000');
});
