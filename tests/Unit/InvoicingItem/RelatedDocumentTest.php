<?php

use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('can assign a related document', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);
    $item->setRelatedDocument('FT 01P2025/1', 1);

    expect($item->relatedDocument()->get('document_id'))->toBe('FT 01P2025/1');
    expect($item->relatedDocument()->get('row'))->toBe(1);
});

it('can override the line of a related document', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);
    $item->setRelatedDocument('FT 01P2025/1', 1);

    expect($item->relatedDocument()->get('document_id'))->toBe('FT 01P2025/1');
    expect($item->relatedDocument()->get('row'))->toBe(1);

    $item->setRelatedDocument('FT 01P2025/1', 2);

    expect($item->relatedDocument()->get('document_id'))->toBe('FT 01P2025/1');
    expect($item->relatedDocument()->get('row'))->toBe(2);
});

it('is empty when no related document was set', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->relatedDocument()->isEmpty())->toBeTrue();
});