<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\InvoicingItem;

it('can assign quantity', function () {
    $item = new InvoicingItem(reference: 'reference-1', quantity: 3);
    $item->setPrice(500);

    expect($item->quantity)->toBe(3);
});

it('defaults quantity to 1', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->quantity)->toBe(1);
});
