<?php

use CsarCrr\InvoicingIntegration\InvoiceItem;

it('can assign reference', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->reference)->toBe('reference-1');
});
