<?php

use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('can assign note', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);
    $item->setNote('Test note');

    expect($item->note())->toBe('Test note');
});

it('has note null by default', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->note())->toBeNull();
});
