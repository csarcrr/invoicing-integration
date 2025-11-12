<?php

use CsarCrr\InvoicingIntegration\InvoiceItem;

it('can assign price', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    expect($item->price())->toBe(500);
});
