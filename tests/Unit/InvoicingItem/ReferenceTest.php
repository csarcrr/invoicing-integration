<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\InvoicingItem;

it('can assign reference', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->reference)->toBe('reference-1');
});
