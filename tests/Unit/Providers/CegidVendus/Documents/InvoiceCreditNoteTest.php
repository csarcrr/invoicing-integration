<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('has a valid payload', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setType(DocumentType::CreditNote);
    $invoicing->addRelatedDocument('FT 01P2025/1');
    $invoicing->setCreditNoteReason('Product returned by customer');

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('notes'))->toBe('Product returned by customer');
    expect($resolve->payload()->get('related_document_id'))->toBe('FT 01P2025/1');
});
