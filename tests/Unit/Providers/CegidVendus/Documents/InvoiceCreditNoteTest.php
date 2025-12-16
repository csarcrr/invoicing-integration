<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('has a valid payload', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);
    $item->setRelatedDocument('FT 01P2025/1', 1);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setType(DocumentType::CreditNote);

    $invoicing->setCreditNoteReason('Product returned by customer');

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    $item = $resolve->payload()->get('items')->first();

    expect($resolve->payload()->get('notes'))->toBe('Product returned by customer');
    expect($item['related_document']['document_number'])
        ->toBe('FT 01P2025/1');
    expect($item['related_document']['document_row'])
        ->toBe(1);
});

it('fails when no related document was set in every item', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $item2 = new InvoiceItem(reference: 'reference-2');
    $item2->setPrice(300);
    $item2->setRelatedDocument('FT 01P2025/1', 1);

    $invoicing = Invoice::create();

    $invoicing->addItem($item);
    $invoicing->addItem($item2);

    $invoicing->setType(DocumentType::CreditNote);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();
})->throws(
    InvoiceItemIsNotValidException::class,
    'Credit Note items must have a related document set.'
);
