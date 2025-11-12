<?php

use CsarCrr\InvoicingIntegration\InvoicingItem;

it('can assign price', function () {
    $item = new InvoicingItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->price())->toBe(500);
});
