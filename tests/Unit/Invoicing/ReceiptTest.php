<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

it('can assign related documents to a receipt', function () {
    $this->invoice->setType(DocumentType::Receipt);
    $this->invoice->addRelatedDocument('FT 1000');

    expect($this->invoice->relatedDocuments()->count())->toBe(1);
    expect($this->invoice->relatedDocuments()->first())->toBe('FT 1000');
});
