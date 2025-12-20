<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('can set a related document', function () {
    $this->invoice->setType(DocumentType::CreditNote);
    $this->invoice->addRelatedDocument('FT 01P2025/1');

    expect($this->invoice->relatedDocuments())->toContain('FT 01P2025/1');
});
